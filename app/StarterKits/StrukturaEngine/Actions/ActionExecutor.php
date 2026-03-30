<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\Models\EngineLog;
use App\StarterKits\StrukturaEngine\Contracts\GuardContract;
use App\StarterKits\StrukturaEngine\Contracts\HandlesWorkflowAction;
use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\Exceptions\GuardException;
use App\StarterKits\StrukturaEngine\Workflow\StrukturaStateMachine;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;
use App\Support\Engine\Ast\EngineNode;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActionExecutor
{
    public function __construct(
        private readonly ActionRegistry $actionRegistry,
        private readonly StrukturaStateMachine $stateMachine,
        private readonly EffectDispatcher $effectDispatcher,
        private readonly AuthFactory $authFactory,
    ) {}

    public function availabilityFor(EngineNode $actionNode, ?Model $record): GuardDecision
    {
        if (! $record) {
            return GuardDecision::hide('This workflow action requires an Eloquent record.');
        }

        $definition = $this->actionRegistry->definitionForNode($actionNode, $record);

        if (! $definition) {
            return GuardDecision::hide('This workflow action is not registered for the selected model.');
        }

        $context = new WorkflowActionContext(
            definition: $definition,
            actionNode: $actionNode,
            record: $record,
            actor: $this->actor(),
            operation: 'availability',
        );

        $decision = $this->stateMachine->availabilityFor($definition, $record);
        $guard = $this->guardFor($definition);

        if (! $guard) {
            return $decision;
        }

        try {
            return $decision->merge(
                $guard->evaluate($context),
            );
        } catch (GuardException $exception) {
            return new GuardDecision(
                visible: $decision->visible,
                enabled: false,
                reason: $exception->getMessage(),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultDataFor(EngineNode $actionNode, ?Model $record): array
    {
        if (! $record) {
            return [];
        }

        $definition = $this->actionRegistry->definitionForNode($actionNode, $record);

        if (! $definition) {
            return [];
        }

        $handler = $this->handlerFor($definition);

        return $handler->defaultData(new WorkflowActionContext(
            definition: $definition,
            actionNode: $actionNode,
            record: $record,
            actor: $this->actor(),
            operation: 'prefill',
        ));
    }

    public function executeAction(EngineNode $actionNode, Model $record, array $data = []): ActionOutcome
    {
        $definition = $this->actionRegistry->definitionForNode($actionNode, $record);

        if (! $definition) {
            throw ValidationException::withMessages([
                'action' => 'This action is not registered for the selected model.',
            ]);
        }

        $availability = $this->availabilityFor($actionNode, $record);

        if (! $availability->enabled) {
            throw ValidationException::withMessages([
                'action' => $availability->reason ?? 'This action cannot be executed right now.',
            ]);
        }

        $context = new WorkflowActionContext(
            definition: $definition,
            actionNode: $actionNode,
            record: $record,
            actor: $this->actor(),
            data: $data,
            operation: 'execute',
        );

        $beforeAttributes = $record->getAttributes();
        $handler = $this->handlerFor($definition);

        return DB::transaction(function () use ($context, $definition, $record, $beforeAttributes, $handler): ActionOutcome {
            $outcome = $handler->handle($context);
            $outcome = $this->effectDispatcher->dispatch($context, $outcome);

            if ($outcome->transitionTo !== null) {
                $this->stateMachine->transition($definition, $record, $outcome->transitionTo);
            }

            $record->refresh();

            $this->writeAuditLog($context, $beforeAttributes, $record, $outcome);

            return $outcome;
        });
    }

    private function guardFor(ActionDefinition $definition): ?GuardContract
    {
        if (! is_string($definition->guardClass) || $definition->guardClass === '') {
            return null;
        }

        $guard = app($definition->guardClass);

        return $guard instanceof GuardContract ? $guard : null;
    }

    private function handlerFor(ActionDefinition $definition): HandlesWorkflowAction
    {
        $handler = app($definition->handlerClass);

        if (! $handler instanceof HandlesWorkflowAction) {
            throw ValidationException::withMessages([
                'action' => 'The registered workflow handler is invalid.',
            ]);
        }

        return $handler;
    }

    /**
     * @param  array<string, mixed>  $beforeAttributes
     */
    private function writeAuditLog(
        WorkflowActionContext $context,
        array $beforeAttributes,
        Model $record,
        ActionOutcome $outcome,
    ): void {
        $afterAttributes = $record->getAttributes();
        $changedKeys = array_values(array_unique([
            ...array_keys(array_diff_assoc($afterAttributes, $beforeAttributes)),
            ...array_keys(array_diff_assoc($beforeAttributes, $afterAttributes)),
        ]));

        $oldValues = Arr::only($beforeAttributes, $changedKeys);
        $newValues = Arr::only($afterAttributes, $changedKeys);

        $actor = $context->actor;

        EngineLog::query()->create([
            'action_name' => $context->actionName(),
            'event' => $context->definition->event,
            'status' => 'executed',
            'subject_type' => $record::class,
            'subject_id' => $record->getKey(),
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->getAuthIdentifier(),
            'old_values' => $oldValues !== [] ? $oldValues : null,
            'new_values' => $newValues !== [] ? $newValues : null,
            'payload' => [
                'data' => $context->data,
                'meta' => $outcome->meta,
            ],
        ]);
    }

    private function actor(): ?Authenticatable
    {
        $user = $this->authFactory->guard()->user();

        return $user instanceof Authenticatable ? $user : null;
    }
}

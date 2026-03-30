<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\Support\Engine\Ast\EngineNode;
use Illuminate\Database\Eloquent\Model;

class ActionRegistry
{
    /**
     * @return array<int, ActionDefinition>
     */
    public function definitionsForModel(Model|string $record): array
    {
        $recordClass = $record instanceof Model ? $record::class : $record;
        $workflowModels = config('struktura-engine.workflow.models', []);

        if (! is_array($workflowModels)) {
            return [];
        }

        $definitions = [];

        foreach ($workflowModels as $modelClass => $configuration) {
            if (! is_string($modelClass) || ! is_array($configuration) || ! is_a($recordClass, $modelClass, true)) {
                continue;
            }

            foreach ($configuration['actions'] ?? [] as $actionName => $actionConfiguration) {
                if (! is_string($actionName) || ! is_array($actionConfiguration)) {
                    continue;
                }

                $definition = $this->definitionFromConfig($modelClass, $actionName, $configuration, $actionConfiguration);

                if ($definition) {
                    $definitions[] = $definition;
                }
            }
        }

        return $definitions;
    }

    public function definitionForNode(EngineNode $node, Model|string $record): ?ActionDefinition
    {
        $actionName = $this->stringValue($node->props['action_name'] ?? null);

        if ($actionName === null) {
            return null;
        }

        foreach ($this->definitionsForModel($record) as $definition) {
            if ($definition->name !== $actionName) {
                continue;
            }

            if (! $this->nodeMatchesDefinition($node, $definition)) {
                continue;
            }

            return $definition;
        }

        return null;
    }

    private function definitionFromConfig(string $modelClass, string $actionName, array $modelConfiguration, array $actionConfiguration): ?ActionDefinition
    {
        $handlerClass = $this->stringValue($actionConfiguration['handler'] ?? null);

        if ($handlerClass === null) {
            return null;
        }

        $guardAlias = $this->stringValue($actionConfiguration['guard'] ?? null);
        $guardClass = $guardAlias ? $this->guardClassForAlias($guardAlias) : null;
        $event = $this->stringValue($actionConfiguration['event'] ?? null) ?? $actionName;
        $stateField = $this->stringValue($modelConfiguration['state_field'] ?? null) ?? 'status';

        return new ActionDefinition(
            name: $actionName,
            modelClass: $modelClass,
            handlerClass: $handlerClass,
            guardAlias: $guardAlias,
            guardClass: $guardClass,
            event: $event,
            fromStates: $this->normalizedStates($actionConfiguration['from'] ?? []),
            toState: $this->stringValue($actionConfiguration['to'] ?? null),
            stateField: $stateField,
        );
    }

    private function nodeMatchesDefinition(EngineNode $node, ActionDefinition $definition): bool
    {
        $handler = $this->stringValue($node->props['handler'] ?? null);

        if ($handler !== null && $handler !== $definition->handlerClass) {
            return false;
        }

        $guardAlias = $this->stringValue($node->props['guard'] ?? null);

        if ($guardAlias !== null && $guardAlias !== $definition->guardAlias) {
            return false;
        }

        $transitionFrom = $this->stringValue($node->props['transition_from'] ?? null);

        if ($transitionFrom !== null && ! in_array($transitionFrom, $definition->fromStates, true)) {
            return false;
        }

        $transitionTo = $this->stringValue($node->props['transition_to'] ?? null);

        if ($transitionTo !== null && $transitionTo !== $definition->toState) {
            return false;
        }

        return true;
    }

    private function guardClassForAlias(string $alias): ?string
    {
        $guards = config('struktura-engine.workflow.guards', []);

        if (! is_array($guards)) {
            return null;
        }

        $guardClass = $guards[$alias] ?? null;

        return is_string($guardClass) && trim($guardClass) !== ''
            ? trim($guardClass)
            : null;
    }

    /**
     * @param  mixed  $states
     * @return array<int, string>
     */
    private function normalizedStates(mixed $states): array
    {
        if (! is_array($states)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $state): ?string => $this->stringValue($state),
            $states,
        )));
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }
}

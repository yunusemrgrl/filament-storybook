<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\StarterKits\StrukturaEngine\Actions\ActionExecutor;
use App\StarterKits\StrukturaEngine\Compilers\ComputedNodeCompiler;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;

class ActionNodeCompiler implements CompilesEngineNode
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
        private readonly ComputedNodeCompiler $computedNodeCompiler,
        private readonly ActionExecutor $actionExecutor,
    ) {}

    public function supports(EngineNode $node): bool
    {
        return $this->nodeRuleMatrix->familyForType($node->type) === NodeRuleMatrix::FAMILY_ACTION;
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode
    {
        $computedChildren = $this->computedNodeCompiler->apply($compiledChildren);
        $action = $this->compileAction($node, $computedChildren);
        $artifact = $this->parentFamily($context) === NodeRuleMatrix::FAMILY_TABLE_WIDGET
            ? $action
            : SchemaActions::make([$action]);

        return new CompiledNode(
            node: $node,
            family: NodeRuleMatrix::FAMILY_ACTION,
            compiler: static::class,
            artifact: $artifact,
            runtimeClass: $action::class,
            summary: [
                'actionName' => is_string($node->props['action_name'] ?? null) ? $node->props['action_name'] : null,
                'event' => is_string($node->props['event'] ?? null) ? $node->props['event'] : null,
                'handler' => is_string($node->props['handler'] ?? null) ? $node->props['handler'] : null,
                'transitionFrom' => is_string($node->props['transition_from'] ?? null) ? $node->props['transition_from'] : null,
                'transitionTo' => is_string($node->props['transition_to'] ?? null) ? $node->props['transition_to'] : null,
                'guard' => is_string($node->props['guard'] ?? null) ? $node->props['guard'] : null,
                'triggerStyle' => is_string($node->props['trigger_style'] ?? null) ? $node->props['trigger_style'] : null,
                'schemaFields' => count($computedChildren),
                'operation' => $context->operation,
                'parentType' => $context->parentType,
                'mode' => $context->mode,
            ],
            children: $computedChildren,
        );
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    private function compileAction(EngineNode $node, array $compiledChildren): Action
    {
        $action = Action::make((string) ($node->props['action_name'] ?? $node->id))
            ->label((string) ($node->props['label'] ?? $node->label))
            ->color((string) ($node->props['color'] ?? 'primary'))
            ->fillForm(fn (Model $record): array => $this->actionExecutor->defaultDataFor($node, $record))
            ->visible(fn (?Model $record): bool => $this->actionExecutor->availabilityFor($node, $record)->visible)
            ->disabled(fn (?Model $record): bool => ! $this->actionExecutor->availabilityFor($node, $record)->enabled)
            ->tooltip(function (?Model $record) use ($node): ?string {
                $decision = $this->actionExecutor->availabilityFor($node, $record);

                return $decision->enabled ? null : $decision->reason;
            });

        $action->action(function (array $data, Model $record) use ($node): void {
            $this->actionExecutor->executeAction($node, $record, $data);
        });

        if ($compiledChildren !== []) {
            $action->schema(array_values(array_map(
                fn (CompiledNode $child): SchemaComponent|Field => $this->toSchemaComponent($child),
                $compiledChildren,
            )));

            if (($node->props['trigger_style'] ?? null) === 'slide_over') {
                $action->slideOver();
            }

            $action->modalHeading((string) ($node->props['label'] ?? $node->label));
        } elseif ((bool) ($node->props['requires_confirmation'] ?? false)) {
            $action->requiresConfirmation();
        }

        return $action;
    }

    private function parentFamily(CompileContext $context): ?string
    {
        return $context->parentType !== null
            ? $this->nodeRuleMatrix->familyForType($context->parentType)
            : null;
    }

    private function toSchemaComponent(CompiledNode $compiledNode): SchemaComponent|Field
    {
        if ($compiledNode->artifact instanceof SchemaComponent || $compiledNode->artifact instanceof Field) {
            return $compiledNode->artifact;
        }

        return View::make('filament.schemas.components.struktura-runtime-node')
            ->viewData([
                'compiledNode' => $compiledNode,
            ]);
    }
}

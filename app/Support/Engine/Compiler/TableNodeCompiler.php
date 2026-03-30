<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\StarterKits\StrukturaEngine\Workflow\StrukturaStateMachine;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use BackedEnum;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class TableNodeCompiler implements CompilesEngineNode
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
        private readonly StrukturaStateMachine $stateMachine,
    ) {}

    public function supports(EngineNode $node): bool
    {
        return $this->nodeRuleMatrix->familyForType($node->type) === NodeRuleMatrix::FAMILY_TABLE_COLUMN;
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode
    {
        $canonicalType = $this->nodeRuleMatrix->canonicalType($node->type);
        $columnPath = $this->resolveColumnPath($node);
        $artifact = match ($canonicalType) {
            'filament.table.text_column' => $this->compileTextColumn($node, $columnPath),
            'filament.table.image_column' => $this->compileImageColumn($node, $columnPath),
            'filament.table.badge_column' => $this->compileBadgeColumn($node, $columnPath),
            'filament.table.icon_column' => $this->compileIconColumn($node, $columnPath),
            default => null,
        };

        return new CompiledNode(
            node: $node,
            family: NodeRuleMatrix::FAMILY_TABLE_COLUMN,
            compiler: static::class,
            artifact: $artifact,
            runtimeClass: is_object($artifact) ? $artifact::class : null,
            summary: [
                'payloadPath' => is_string($node->props['payload_path'] ?? null) ? $node->props['payload_path'] : null,
                'columnPath' => $columnPath,
                'dataSourceModel' => is_string($node->props['data_source_model'] ?? null) ? $node->props['data_source_model'] : null,
                'relationship' => is_string($node->props['relationship'] ?? null) ? $node->props['relationship'] : null,
                'hydrationLogic' => is_string($node->props['hydration_logic'] ?? null) ? $node->props['hydration_logic'] : null,
                'searchable' => (bool) ($node->props['is_searchable'] ?? false),
                'sortable' => (bool) ($node->props['is_sortable'] ?? false),
                'mode' => $context->mode,
            ],
            children: [],
        );
    }

    private function compileTextColumn(EngineNode $node, string $columnPath): TextColumn
    {
        return TextColumn::make($columnPath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->searchable((bool) ($node->props['is_searchable'] ?? false))
            ->sortable((bool) ($node->props['is_sortable'] ?? false));
    }

    private function compileImageColumn(EngineNode $node, string $columnPath): ImageColumn
    {
        $component = ImageColumn::make($columnPath)
            ->label((string) ($node->props['label'] ?? $node->label));

        if (is_string($node->props['disk'] ?? null) && trim((string) $node->props['disk']) !== '') {
            $component->disk((string) $node->props['disk']);
        }

        if ((bool) ($node->props['is_circular'] ?? false)) {
            $component->circular();
        }

        return $component;
    }

    private function compileBadgeColumn(EngineNode $node, string $columnPath): BadgeColumn
    {
        return BadgeColumn::make($columnPath)
            ->badge()
            ->label((string) ($node->props['label'] ?? $node->label))
            ->searchable((bool) ($node->props['is_searchable'] ?? false))
            ->sortable((bool) ($node->props['is_sortable'] ?? false))
            ->color(fn (mixed $state): ?string => $this->badgeColorFor($node, $state));
    }

    private function compileIconColumn(EngineNode $node, string $columnPath): IconColumn
    {
        return IconColumn::make($columnPath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->boolean((bool) ($node->props['is_boolean'] ?? false));
    }

    private function resolveColumnPath(EngineNode $node): string
    {
        foreach (['column_path', 'column_name', 'payload_path'] as $candidate) {
            $value = $node->props[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return 'column';
    }

    private function badgeColorFor(EngineNode $node, mixed $state): ?string
    {
        $normalizedState = $this->normalizeState($state);
        $modelClass = is_string($node->props['data_source_model'] ?? null) ? trim((string) $node->props['data_source_model']) : '';

        if ($normalizedState !== null && $modelClass !== '') {
            $color = $this->stateMachine->colorFor($modelClass, $normalizedState);

            if ($color !== null) {
                return $color;
            }
        }

        $stateColors = is_array($node->props['state_colors'] ?? null) ? $node->props['state_colors'] : [];

        foreach ($stateColors as $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $candidateState = $definition['state'] ?? null;
            $candidateColor = $definition['color'] ?? null;

            if ($candidateState === $normalizedState && is_string($candidateColor) && trim($candidateColor) !== '') {
                return trim($candidateColor);
            }
        }

        return null;
    }

    private function normalizeState(mixed $state): ?string
    {
        if ($state instanceof BackedEnum) {
            return is_string($state->value) && trim($state->value) !== ''
                ? trim($state->value)
                : null;
        }

        if ($state instanceof UnitEnum) {
            return trim($state->name) !== ''
                ? trim($state->name)
                : null;
        }

        return is_string($state) && trim($state) !== ''
            ? trim($state)
            : null;
    }
}

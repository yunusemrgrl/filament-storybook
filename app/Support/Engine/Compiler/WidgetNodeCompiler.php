<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\StarterKits\StrukturaEngine\Workflow\RuntimeTableWidget;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use Filament\Schemas\Components\Livewire;

class WidgetNodeCompiler implements CompilesEngineNode
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
    ) {}

    public function supports(EngineNode $node): bool
    {
        return in_array(
            $this->nodeRuleMatrix->familyForType($node->type),
            [NodeRuleMatrix::FAMILY_WIDGET, NodeRuleMatrix::FAMILY_TABLE_WIDGET],
            true,
        );
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode
    {
        $canonicalType = $this->nodeRuleMatrix->canonicalType($node->type);
        $runtimeClass = match ($canonicalType) {
            'filament.widget.stats_overview' => 'Filament\\Widgets\\StatsOverviewWidget',
            'filament.widget.chart_widget' => 'Filament\\Widgets\\ChartWidget',
            'filament.widget.table_widget' => RuntimeTableWidget::class,
            default => 'Filament\\Widgets\\Widget',
        };

        $summary = [
            'payloadPath' => is_string($node->props['payload_path'] ?? null) ? $node->props['payload_path'] : null,
            'widgetKey' => is_string($node->props['widget_key'] ?? null) ? $node->props['widget_key'] : null,
            'dataSourceModel' => is_string($node->props['data_source_model'] ?? null) ? $node->props['data_source_model'] : null,
            'queryScope' => is_string($node->props['query_scope'] ?? null) ? $node->props['query_scope'] : null,
            'hydrationLogic' => is_string($node->props['hydration_logic'] ?? null) ? $node->props['hydration_logic'] : null,
            'childColumns' => count($compiledChildren),
            'childActions' => count(array_filter(
                $compiledChildren,
                fn (CompiledNode $child): bool => $child->family === NodeRuleMatrix::FAMILY_ACTION,
            )),
            'mode' => $context->mode,
        ];

        if ($canonicalType === 'filament.widget.chart_widget') {
            $summary['chartType'] = is_string($node->props['chart_type'] ?? null) ? $node->props['chart_type'] : null;
            $summary['aggregate'] = is_string($node->props['aggregate'] ?? null) ? $node->props['aggregate'] : null;
            $summary['groupBy'] = is_string($node->props['group_by'] ?? null) ? $node->props['group_by'] : null;
        }

        if ($canonicalType === 'filament.widget.stats_overview') {
            $stats = is_array($node->props['stats'] ?? null) ? $node->props['stats'] : [];
            $summary['stats'] = count($stats);
        }

        return new CompiledNode(
            node: $node,
            family: $this->nodeRuleMatrix->familyForType($node->type),
            compiler: static::class,
            artifact: $canonicalType === 'filament.widget.table_widget'
                ? Livewire::make(RuntimeTableWidget::class, [
                    'node' => $node->toArray(),
                    'mode' => $context->mode,
                ])
                : [
                    'runtimeClass' => $runtimeClass,
                    'payload' => $node->props,
                ],
            runtimeClass: $runtimeClass,
            summary: $summary,
            children: $compiledChildren,
        );
    }
}

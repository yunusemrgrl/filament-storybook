<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Workflow;

use App\ComponentSurface;
use App\StarterKits\StrukturaEngine\Services\EngineCompilerRuntime;
use App\Support\Engine\Compiler\CompiledNode;
use App\Support\Engine\NodeRuleMatrix;
use Filament\Actions\Action;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class RuntimeTableWidget extends TableWidget
{
    /**
     * @var array<string, mixed>
     */
    public array $node = [];

    public string $mode = 'runtime';

    public function table(Table $table): Table
    {
        $compiledNode = $this->compiledNode();
        $modelClass = $compiledNode->summary['dataSourceModel'] ?? null;

        if (! is_string($modelClass) || ! is_a($modelClass, Model::class, true)) {
            throw new InvalidArgumentException('Runtime table widgets require a valid Eloquent model class.');
        }

        $query = app(QueryScopeApplier::class)->apply(
            $modelClass::query(),
            is_string($compiledNode->summary['queryScope'] ?? null) ? $compiledNode->summary['queryScope'] : null,
        );

        $paginationSize = (int) ($this->node['props']['pagination_size'] ?? 25);

        return $table
            ->heading($compiledNode->node->label)
            ->query($query)
            ->columns($this->columns($compiledNode))
            ->recordActions($this->actions($compiledNode))
            ->defaultPaginationPageOption($paginationSize)
            ->paginationPageOptions([$paginationSize]);
    }

    private function compiledNode(): CompiledNode
    {
        $compiledNodes = app(EngineCompilerRuntime::class)->compile(
            ComponentSurface::Page,
            [$this->node],
            $this->mode,
            operation: 'table',
        );

        $compiledNode = $compiledNodes[0] ?? null;

        if (! $compiledNode instanceof CompiledNode) {
            throw new InvalidArgumentException('Unable to compile the provided runtime table node.');
        }

        return $compiledNode;
    }

    /**
     * @return array<int, Column>
     */
    private function columns(CompiledNode $compiledNode): array
    {
        return array_values(array_filter(array_map(
            fn (CompiledNode $child): ?Column => $child->family === NodeRuleMatrix::FAMILY_TABLE_COLUMN && $child->artifact instanceof Column
                ? $child->artifact
                : null,
            $compiledNode->children,
        )));
    }

    /**
     * @return array<int, Action>
     */
    private function actions(CompiledNode $compiledNode): array
    {
        return array_values(array_filter(array_map(
            fn (CompiledNode $child): ?Action => $child->family === NodeRuleMatrix::FAMILY_ACTION && $child->artifact instanceof Action
                ? $child->artifact
                : null,
            $compiledNode->children,
        )));
    }
}

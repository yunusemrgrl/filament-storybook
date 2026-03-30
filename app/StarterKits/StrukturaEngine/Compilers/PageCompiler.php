<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Compilers;

use App\ComponentSurface;
use App\Filament\Widgets\DynamicStrukturaCompiledWidget;
use App\Models\Page;
use App\StarterKits\StrukturaEngine\Contracts\CompilesPageAst;
use App\StarterKits\StrukturaEngine\Services\EngineCompilerRuntime;
use App\StarterKits\StrukturaEngine\Workflow\RuntimeTableWidget;
use App\Support\Engine\Ast\EngineNodeCollection;
use App\Support\Engine\Compiler\CompiledNode;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\View;
use Filament\Widgets\WidgetConfiguration;

class PageCompiler implements CompilesPageAst
{
    public function __construct(
        private readonly EngineCompilerRuntime $runtime,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection|null  $nodes
     * @return array<int, CompiledNode>
     */
    public function compileNodes(Page $page, array|EngineNodeCollection|null $nodes = null, string $mode = 'runtime'): array
    {
        return $this->runtime->compile(
            ComponentSurface::Page,
            $nodes ?? $page->blocks,
            $mode,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection|null  $nodes
     * @return array<int, SchemaComponent|Field>
     */
    public function compileContentComponents(Page $page, array|EngineNodeCollection|null $nodes = null, string $mode = 'runtime'): array
    {
        return array_values(array_map(
            fn (CompiledNode $compiledNode): SchemaComponent|Field => $this->compiledNodeToSchemaComponent($compiledNode),
            $this->compileNodes($page, $nodes, $mode),
        ));
    }

    /**
     * @return array<int, WidgetConfiguration>
     */
    public function compileWidgetConfigurations(Page $page, string $mode = 'runtime'): array
    {
        return array_values(array_map(
            fn (CompiledNode $compiledNode): WidgetConfiguration => $compiledNode->runtimeClass === RuntimeTableWidget::class
                ? RuntimeTableWidget::make([
                    'node' => $compiledNode->node->toArray(),
                    'mode' => $mode,
                ])
                : DynamicStrukturaCompiledWidget::make([
                    'pageId' => $page->getKey(),
                    'nodeId' => $compiledNode->node->id,
                    'mode' => $mode,
                ]),
            array_filter(
                $this->flattenCompiledNodes($this->compileNodes($page, mode: $mode)),
                static fn (CompiledNode $compiledNode): bool => in_array($compiledNode->family, ['widget', 'table-widget'], true),
            ),
        ));
    }

    private function compiledNodeToSchemaComponent(CompiledNode $compiledNode): SchemaComponent|Field
    {
        if ($compiledNode->artifact instanceof SchemaComponent || $compiledNode->artifact instanceof Field) {
            return $compiledNode->artifact;
        }

        return View::make('filament.schemas.components.struktura-runtime-node')
            ->viewData([
                'compiledNode' => $compiledNode,
            ]);
    }

    /**
     * @param  array<int, CompiledNode>  $compiledNodes
     * @return array<int, CompiledNode>
     */
    private function flattenCompiledNodes(array $compiledNodes): array
    {
        $flattened = [];

        foreach ($compiledNodes as $compiledNode) {
            $flattened[] = $compiledNode;

            if ($compiledNode->children !== []) {
                $flattened = [
                    ...$flattened,
                    ...$this->flattenCompiledNodes($compiledNode->children),
                ];
            }
        }

        return $flattened;
    }
}

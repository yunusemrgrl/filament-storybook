<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Page;
use App\StarterKits\StrukturaEngine\Compilers\PageCompiler;
use App\Support\Engine\Compiler\CompiledNode;
use Filament\Widgets\Widget;

class DynamicStrukturaCompiledWidget extends Widget
{
    protected string $view = 'filament.widgets.dynamic-struktura-compiled-widget';

    public int $pageId;

    public string $nodeId;

    public string $mode = 'runtime';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $page = Page::query()->find($this->pageId);

        if (! $page) {
            return [
                'title' => 'Unavailable widget',
                'summary' => [],
            ];
        }

        $compiledNode = $this->findCompiledNode(
            app(PageCompiler::class)->compileNodes($page, mode: $this->mode),
            $this->nodeId,
        );

        if (! $compiledNode) {
            return [
                'title' => $page->title,
                'summary' => [],
            ];
        }

        return [
            'title' => $compiledNode->node->label,
            'summary' => $compiledNode->summary,
            'runtimeClass' => $compiledNode->runtimeClass,
        ];
    }

    /**
     * @param  array<int, CompiledNode>  $compiledNodes
     */
    private function findCompiledNode(array $compiledNodes, string $nodeId): ?CompiledNode
    {
        foreach ($compiledNodes as $compiledNode) {
            if ($compiledNode->node->id === $nodeId) {
                return $compiledNode;
            }

            if ($compiledNode->children !== []) {
                $childNode = $this->findCompiledNode($compiledNode->children, $nodeId);

                if ($childNode) {
                    return $childNode;
                }
            }
        }

        return null;
    }
}

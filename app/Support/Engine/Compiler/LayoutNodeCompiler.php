<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\StarterKits\StrukturaEngine\Compilers\ComputedNodeCompiler;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

class LayoutNodeCompiler implements CompilesEngineNode
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
        private readonly ComputedNodeCompiler $computedNodeCompiler,
    ) {}

    public function supports(EngineNode $node): bool
    {
        return $this->nodeRuleMatrix->familyForType($node->type) === NodeRuleMatrix::FAMILY_LAYOUT;
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode
    {
        $canonicalType = $this->nodeRuleMatrix->canonicalType($node->type);
        $computedChildren = $this->computedNodeCompiler->apply($compiledChildren);
        $childArtifacts = array_values(array_map(
            fn (CompiledNode $child): SchemaComponent|Field => $this->toSchemaComponent($child),
            $computedChildren,
        ));

        $artifact = match ($canonicalType) {
            'filament.layout.grid' => Grid::make($node->props['columns'] ?? 2)->components($childArtifacts),
            'filament.layout.section' => Section::make($node->props['heading'] ?? $node->label)
                ->description($node->props['description'] ?? null)
                ->components($childArtifacts),
            default => null,
        };

        return new CompiledNode(
            node: $node,
            family: NodeRuleMatrix::FAMILY_LAYOUT,
            compiler: static::class,
            artifact: $artifact,
            runtimeClass: is_object($artifact) ? $artifact::class : null,
            summary: [
                'columns' => $canonicalType === 'filament.layout.grid' ? (int) ($node->props['columns'] ?? 2) : null,
                'heading' => $canonicalType === 'filament.layout.section' ? (string) ($node->props['heading'] ?? $node->label) : null,
                'description' => is_string($node->props['description'] ?? null) ? $node->props['description'] : null,
                'computedChildren' => count(array_filter(
                    $computedChildren,
                    static fn (CompiledNode $child): bool => $child->node->computedLogic() !== [],
                )),
                'children' => count($compiledChildren),
                'mode' => $context->mode,
            ],
            children: $computedChildren,
        );
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

<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\ComponentSurface;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Ast\EngineNodeCollection;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use Illuminate\Database\Eloquent\Model;

class EngineCompilerRuntime
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection  $nodes
     * @return array<int, CompiledNode>
     */
    public function compile(
        ComponentSurface|string $surface,
        array|EngineNodeCollection $nodes,
        string $mode = 'runtime',
        ?Model $record = null,
        string $operation = 'render',
    ): array {
        $collection = $nodes instanceof EngineNodeCollection
            ? $nodes
            : EngineNodeCollection::fromArray($nodes);

        $context = new CompileContext(
            surface: $surface instanceof ComponentSurface ? $surface : ComponentSurface::tryFrom($surface) ?? ComponentSurface::Page,
            mode: $mode,
            operation: $operation,
            record: $record,
        );

        return array_map(
            fn (EngineNode $node): CompiledNode => $this->compileNode($node, $context),
            $collection->all(),
        );
    }

    private function compileNode(EngineNode $node, CompileContext $context): CompiledNode
    {
        $childContext = $context->forChildNode($node);

        $compiledChildren = array_map(
            fn (EngineNode $child): CompiledNode => $this->compileNode($child, $childContext),
            $node->children->all(),
        );

        foreach ($this->compilers() as $compiler) {
            if (! $compiler->supports($node)) {
                continue;
            }

            return $compiler->compile($node, $compiledChildren, $context);
        }

        return new CompiledNode(
            node: $node,
            family: $this->nodeRuleMatrix->familyForType($node->type),
            compiler: static::class,
            artifact: null,
            runtimeClass: null,
            summary: [
                'mode' => $context->mode,
                'message' => 'No compiler matched this node type.',
            ],
            children: $compiledChildren,
        );
    }

    /**
     * @return array<int, CompilesEngineNode>
     */
    private function compilers(): array
    {
        return [
            app(LayoutNodeCompiler::class),
            app(FormNodeCompiler::class),
            app(ActionNodeCompiler::class),
            app(TableNodeCompiler::class),
            app(WidgetNodeCompiler::class),
        ];
    }
}

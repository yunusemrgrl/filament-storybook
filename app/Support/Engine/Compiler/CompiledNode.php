<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\Support\Engine\Ast\EngineNode;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class CompiledNode implements Arrayable
{
    /**
     * @param  array<int, self>  $children
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public EngineNode $node,
        public string $family,
        public string $compiler,
        public mixed $artifact,
        public ?string $runtimeClass,
        public array $summary,
        public array $children = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->node->id,
            'type' => $this->node->type,
            'slug' => $this->node->slug(),
            'label' => $this->node->label,
            'family' => $this->family,
            'compiler' => $this->compiler,
            'runtimeClass' => $this->runtimeClass,
            'summary' => $this->summary,
            'children' => array_map(
                static fn (self $child): array => $child->toArray(),
                $this->children,
            ),
        ];
    }
}

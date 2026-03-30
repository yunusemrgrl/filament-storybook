<?php

declare(strict_types=1);

namespace App\Support\Engine\Ast;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Traversable;

/**
 * @implements Arrayable<int, array<string, mixed>>
 * @implements IteratorAggregate<int, EngineNode>
 */
readonly class EngineNodeCollection implements Arrayable, Countable, IteratorAggregate
{
    /**
     * @param  array<int, EngineNode>  $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    public static function empty(): self
    {
        return new self;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function fromArray(array $items): self
    {
        return new self(array_values(array_map(
            static fn (array $item): EngineNode => EngineNode::fromArray($item),
            array_values(array_filter($items, 'is_array')),
        )));
    }

    /**
     * @return array<int, EngineNode>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (EngineNode $node): array => $node->toArray(),
            $this->items,
        );
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

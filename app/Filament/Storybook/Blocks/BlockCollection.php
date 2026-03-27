<?php

namespace App\Filament\Storybook\Blocks;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements Arrayable<int, array<string, mixed>>
 * @implements IteratorAggregate<int, array<string, mixed>>
 */
readonly class BlockCollection implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function fromArray(array $items): static
    {
        $normalizedItems = array_values(array_filter(
            $items,
            static fn (mixed $item): bool => is_array($item),
        ));

        return new static($normalizedItems);
    }

    public static function empty(): static
    {
        return new static;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return array<int, ResolvedBlock>
     */
    public function resolve(): array
    {
        return array_map(
            static fn (array $payload): ResolvedBlock => app(BlockFactory::class)->make($payload),
            $this->items,
        );
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, array<string, mixed>>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

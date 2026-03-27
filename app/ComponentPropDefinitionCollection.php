<?php

namespace App;

use App\Filament\Storybook\KnobDefinition;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Traversable;

/**
 * @implements Arrayable<int, array<string, mixed>>
 * @implements IteratorAggregate<int, ComponentPropDefinition>
 */
readonly class ComponentPropDefinitionCollection implements Arrayable, Countable, IteratorAggregate
{
    /**
     * @param  array<int, ComponentPropDefinition>  $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function fromArray(array $items): self
    {
        return new self(array_values(array_map(
            static fn (array $item): ComponentPropDefinition => ComponentPropDefinition::fromArray($item),
            array_values(array_filter($items, 'is_array')),
        )));
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public function toKnobDefinitions(): array
    {
        return array_map(
            static fn (ComponentPropDefinition $item): KnobDefinition => $item->toKnobDefinition(),
            $this->items,
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalizeValues(array $values): array
    {
        $normalized = [];

        foreach ($this->items as $item) {
            $normalized[$item->name] = $item->normalizeValue($values[$item->name] ?? null);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function makeBuilderData(array $values): array
    {
        $normalized = [];

        foreach ($this->items as $item) {
            $normalized[$item->name] = $item->makeBuilderValue($values[$item->name] ?? null);
        }

        return $normalized;
    }

    public function first(): ?ComponentPropDefinition
    {
        return $this->items[0] ?? null;
    }

    public function firstNamed(string $name): ?ComponentPropDefinition
    {
        foreach ($this->items as $item) {
            if ($item->name === $name) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ComponentPropDefinition $item): array => $item->toArray(),
            $this->items,
        );
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, ComponentPropDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

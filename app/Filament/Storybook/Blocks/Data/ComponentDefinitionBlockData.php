<?php

namespace App\Filament\Storybook\Blocks\Data;

use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Models\ComponentDefinition;
use Illuminate\Support\Arr;
use InvalidArgumentException;

readonly class ComponentDefinitionBlockData implements BlockDataContract
{
    /**
     * @param  array<string, mixed>  $props
     */
    public function __construct(
        public ComponentDefinition $definition,
        public array $props,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static
    {
        $definition = ComponentDefinition::query()
            ->find($payload['component_definition_id'] ?? null);

        if (! $definition && is_string($payload['component_handle'] ?? null)) {
            $definition = ComponentDefinition::query()
                ->where('handle', $payload['component_handle'])
                ->first();
        }

        if (! $definition) {
            throw new InvalidArgumentException('Component definition not found.');
        }

        return new static(
            definition: $definition,
            props: $definition->normalizeProps(Arr::wrap($payload['props'] ?? [])),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromDefinition(ComponentDefinition $definition, array $payload): static
    {
        return new static(
            definition: $definition,
            props: $definition->normalizeProps(Arr::wrap($payload['props'] ?? [])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'definition' => $this->definition->only([
                'id',
                'name',
                'handle',
                'description',
                'category',
                'view',
            ]),
            'props' => $this->props,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        return [
            'componentDefinition' => $this->definition,
            'props' => $this->props,
        ];
    }
}

<?php

namespace App\Support\PageBuilder;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockCollection;
use App\Filament\Storybook\Blocks\BlockRegistry;
use Illuminate\Support\Str;

class EditorStateMapper
{
    public function __construct(
        private readonly EditorSchemaExporter $schemaExporter,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>|BlockCollection  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function toEditorBlocks(ComponentSurface|string $surface, array|BlockCollection $blocks): array
    {
        $payloads = $blocks instanceof BlockCollection ? $blocks->toArray() : BlockCollection::fromArray($blocks)->toArray();

        return array_values(array_filter(array_map(
            fn (array $payload): ?array => $this->payloadToEditorBlock($surface, $payload),
            $payloads,
        )));
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function toPersistedPayload(ComponentSurface|string $surface, array $blocks): array
    {
        $payloads = [];

        foreach ($blocks as $blockState) {
            if (! is_array($blockState)) {
                continue;
            }

            $type = $blockState['type'] ?? null;

            if (! is_string($type)) {
                continue;
            }

            $block = BlockRegistry::findByTypeForSurface($surface, $type);

            if (! $block) {
                continue;
            }

            $data = is_array($blockState['data'] ?? null) ? $blockState['data'] : [];
            $variant = is_string($blockState['variant'] ?? null) ? $blockState['variant'] : 'default';

            $payloads[] = $block->makeBlockPayload(
                $this->schemaExporter->valuesForPersistence($block->knobs(), $data),
                $variant,
            );
        }

        return $payloads;
    }

    /**
     * @return array<string, mixed>
     */
    public function makeEditorBlock(ComponentSurface|string $surface, string $type): ?array
    {
        $block = BlockRegistry::findByTypeForSurface($surface, $type);

        if (! $block) {
            return null;
        }

        return $this->blockToEditorBlock($block, $this->schemaExporter->defaultValuesForBlock($block), 'default');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function payloadToEditorBlock(ComponentSurface|string $surface, array $payload): ?array
    {
        $type = $payload['type'] ?? null;

        if (! is_string($type)) {
            return null;
        }

        $block = BlockRegistry::findByTypeForSurface($surface, $type);

        if (! $block) {
            return null;
        }

        $builderData = $block->makeBuilderData($payload);

        return $this->blockToEditorBlock(
            $block,
            $this->schemaExporter->valuesForEditor($block->knobs(), is_array($builderData) ? $builderData : []),
            is_string($payload['variant'] ?? null) ? $payload['variant'] : 'default',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function blockToEditorBlock(AbstractBlockStory $block, array $data, string $variant): array
    {
        $schema = $this->schemaExporter->exportBlock($block);

        return [
            'id' => (string) Str::uuid(),
            'type' => $schema['type'],
            'label' => $schema['title'],
            'description' => $schema['description'],
            'group' => $schema['group'],
            'icon' => $schema['icon'],
            'view' => $schema['view'],
            'source' => $schema['source'],
            'variant' => $variant,
            'data' => $data,
        ];
    }
}

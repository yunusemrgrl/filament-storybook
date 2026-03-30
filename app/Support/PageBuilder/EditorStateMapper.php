<?php

namespace App\Support\PageBuilder;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Ast\EngineNodeCollection;
use Illuminate\Support\Str;

class EditorStateMapper
{
    public function __construct(
        private readonly EditorSchemaExporter $schemaExporter,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>|EngineNodeCollection  $nodes
     * @return array<int, array<string, mixed>>
     */
    public function toEditorNodes(ComponentSurface|string $surface, array|EngineNodeCollection $nodes): array
    {
        $payloads = $nodes instanceof EngineNodeCollection ? $nodes->toArray() : $nodes;

        return array_values(array_filter(array_map(
            fn (array $payload): ?array => $this->payloadToEditorNode($surface, $payload),
            $payloads,
        )));
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    public function toPersistedPayload(ComponentSurface|string $surface, array $nodes): array
    {
        $payloads = [];

        foreach ($nodes as $nodeState) {
            if (! is_array($nodeState)) {
                continue;
            }

            $type = $nodeState['type'] ?? null;

            if (! is_string($type)) {
                continue;
            }

            $block = BlockRegistry::findByTypeForSurface($surface, $type);

            if (! $block) {
                continue;
            }

            $props = is_array($nodeState['props'] ?? null)
                ? $nodeState['props']
                : (is_array($nodeState['data'] ?? null) ? $nodeState['data'] : []);
            $variant = is_string($nodeState['variant'] ?? null) ? $nodeState['variant'] : 'default';
            $children = is_array($nodeState['children'] ?? null) ? $nodeState['children'] : [];
            $schema = $this->schemaExporter->exportBlock($block);

            $payloads[] = $block->makeBlockPayload(
                $this->schemaExporter->valuesForPersistence($block->knobs(), $props),
                $variant,
            ) + [
                'id' => is_string($nodeState['id'] ?? null) ? $nodeState['id'] : (string) Str::uuid(),
                'surface' => $block->getSurface()->value,
                'label' => is_string($nodeState['label'] ?? null) && trim((string) $nodeState['label']) !== ''
                    ? trim((string) $nodeState['label'])
                    : $schema['title'],
                'children' => $this->toPersistedPayload($surface, $children),
                'computed_logic' => is_array($nodeState['computed_logic'] ?? null) ? $nodeState['computed_logic'] : [],
                'meta' => [
                    'slug' => $schema['slug'],
                    'description' => $schema['description'],
                    'group' => $schema['group'],
                    'icon' => $schema['icon'],
                    'view' => $schema['view'],
                    'source' => $schema['source'],
                    'variant' => $variant,
                    'family' => $schema['family'],
                ],
            ];
        }

        return $payloads;
    }

    /**
     * @return array<string, mixed>
     */
    public function makeEditorNode(ComponentSurface|string $surface, string $type): ?array
    {
        $block = BlockRegistry::findByTypeForSurface($surface, $type);

        if (! $block) {
            return null;
        }

        return $this->blockToEditorNode($block, $this->schemaExporter->defaultValuesForBlock($block), 'default');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function payloadToEditorNode(ComponentSurface|string $surface, array $payload): ?array
    {
        if (array_key_exists('children', $payload) || array_key_exists('meta', $payload) || array_key_exists('props', $payload)) {
            return $this->astNodeToEditorNode(EngineNode::fromArray($payload));
        }

        $type = $payload['type'] ?? null;

        if (! is_string($type)) {
            return null;
        }

        $block = BlockRegistry::findByTypeForSurface($surface, $type);

        if (! $block) {
            return null;
        }

        $builderData = $block->makeBuilderData($payload);

        return $this->blockToEditorNode(
            $block,
            $this->schemaExporter->valuesForEditor($block->knobs(), is_array($builderData) ? $builderData : []),
            is_string($payload['variant'] ?? null) ? $payload['variant'] : 'default',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function blockToEditorNode(AbstractBlockStory $block, array $props, string $variant): array
    {
        $schema = $this->schemaExporter->exportBlock($block);

        return [
            'id' => (string) Str::uuid(),
            'type' => $schema['type'],
            'slug' => $schema['slug'],
            'label' => $schema['title'],
            'description' => $schema['description'],
            'group' => $schema['group'],
            'icon' => $schema['icon'],
            'view' => $schema['view'],
            'source' => $schema['source'],
            'surface' => $schema['surface'],
            'variant' => $variant,
            'family' => $schema['family'],
            'acceptsChildren' => $schema['acceptsChildren'],
            'allowedChildFamilies' => $schema['allowedChildFamilies'],
            'props' => $props,
            'children' => [],
            'computed_logic' => [],
            'meta' => [
                'view' => $schema['view'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function astNodeToEditorNode(EngineNode $node): ?array
    {
        $block = BlockRegistry::findByTypeForSurface($node->surface, $node->type);

        if (! $block) {
            return [
                'id' => $node->id,
                'type' => $node->type,
                'slug' => $node->slug(),
                'label' => $node->label,
                'description' => is_string($node->meta['description'] ?? null) ? $node->meta['description'] : null,
                'group' => is_string($node->meta['group'] ?? null) ? $node->meta['group'] : null,
                'icon' => is_string($node->meta['icon'] ?? null) ? $node->meta['icon'] : null,
                'view' => is_string($node->meta['view'] ?? null) ? $node->meta['view'] : null,
                'source' => $node->source(),
                'surface' => $node->surface->value,
                'variant' => is_string($node->meta['variant'] ?? null) ? $node->meta['variant'] : 'default',
                'family' => $node->meta['family'] ?? null,
                'acceptsChildren' => false,
                'allowedChildFamilies' => [],
                'props' => $node->props,
                'children' => array_values(array_filter(array_map(
                    fn (EngineNode $child): ?array => $this->astNodeToEditorNode($child),
                    $node->children->all(),
                ))),
                'computed_logic' => $node->computedLogic(),
                'meta' => $node->meta,
            ];
        }

        $schema = $this->schemaExporter->exportBlock($block);
        $editorProps = $this->schemaExporter->valuesForEditor($block->knobs(), $node->props);

        return [
            'id' => $node->id,
            'type' => $schema['type'],
            'slug' => $schema['slug'],
            'label' => $node->label,
            'description' => $schema['description'],
            'group' => $schema['group'],
            'icon' => $schema['icon'],
            'view' => $schema['view'],
            'source' => $schema['source'],
            'surface' => $schema['surface'],
            'variant' => is_string($node->meta['variant'] ?? null) ? $node->meta['variant'] : 'default',
            'family' => $schema['family'],
            'acceptsChildren' => $schema['acceptsChildren'],
            'allowedChildFamilies' => $schema['allowedChildFamilies'],
            'props' => $editorProps,
            'children' => array_values(array_filter(array_map(
                fn (EngineNode $child): ?array => $this->astNodeToEditorNode($child),
                $node->children->all(),
            ))),
            'computed_logic' => $node->computedLogic(),
            'meta' => $node->meta,
        ];
    }
}

<?php

namespace App\Support\PageBuilder;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\Blocks\DatabaseComponentBlock;
use App\Filament\Storybook\KnobDefinition;
use App\StarterKits\StrukturaEngine\Services\NodeRuleMatrix;
use Illuminate\Support\Facades\Storage;

class EditorSchemaExporter
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forSurface(ComponentSurface|string $surface): array
    {
        return array_values(array_map(
            fn (AbstractBlockStory $block): array => $this->exportBlock($block),
            BlockRegistry::forSurface($surface),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function exportBlock(AbstractBlockStory $block): array
    {
        $defaults = $this->defaultValuesForBlock($block);
        $source = $block instanceof DatabaseComponentBlock ? 'definition' : 'system';

        return [
            'type' => $block->getBlockType(),
            'slug' => $this->schemaSlug($block, $source),
            'title' => $block->title,
            'description' => $block->description,
            'group' => $block->group,
            'icon' => $block->icon,
            'view' => $block->getFrontendView(),
            'surface' => $block->getSurface()->value,
            'source' => $source,
            'variant' => 'default',
            'defaults' => $defaults,
            'dataSource' => $this->extractDataSource($defaults),
            'family' => $this->nodeRuleMatrix->familyForType($block->getBlockType()),
            'acceptsChildren' => $this->nodeRuleMatrix->acceptsChildren($block->getBlockType()),
            'allowedChildFamilies' => $this->nodeRuleMatrix->allowedChildFamilies($block->getBlockType()),
            'fields' => array_map(
                fn (KnobDefinition $definition): array => $this->exportField($definition),
                $block->knobs(),
            ),
        ];
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function valuesForEditor(array $definitions, array $values): array
    {
        $normalized = [];

        foreach ($definitions as $definition) {
            $name = $definition->getName();
            $normalized[$name] = match ($definition->getType()) {
                KnobDefinition::TYPE_BOOLEAN => (bool) ($values[$name] ?? $definition->getDefault() ?? false),
                KnobDefinition::TYPE_NUMBER => is_numeric($values[$name] ?? null)
                    ? $values[$name] + 0
                    : $definition->getDefault(),
                KnobDefinition::TYPE_SELECT => is_string($values[$name] ?? null)
                    ? $values[$name]
                    : (string) ($definition->getDefault() ?? ''),
                KnobDefinition::TYPE_FILE => $this->fileValueForEditor($definition, $values[$name] ?? $definition->getDefault()),
                KnobDefinition::TYPE_REPEATER => $this->repeaterValuesForEditor($definition, $values[$name] ?? $definition->getDefault()),
                default => is_string($values[$name] ?? null)
                    ? $values[$name]
                    : (string) ($definition->getDefault() ?? ''),
            };
        }

        return $normalized;
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function valuesForPersistence(array $definitions, array $values): array
    {
        $normalized = [];

        foreach ($definitions as $definition) {
            $name = $definition->getName();
            $normalized[$name] = match ($definition->getType()) {
                KnobDefinition::TYPE_BOOLEAN => (bool) ($values[$name] ?? false),
                KnobDefinition::TYPE_NUMBER => is_numeric($values[$name] ?? null) ? $values[$name] + 0 : null,
                KnobDefinition::TYPE_FILE => $this->fileValueForPersistence($values[$name] ?? null),
                KnobDefinition::TYPE_REPEATER => $this->repeaterValuesForPersistence($definition, $values[$name] ?? []),
                default => is_scalar($values[$name] ?? null) ? trim((string) $values[$name]) : '',
            };
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultValuesForBlock(AbstractBlockStory $block): array
    {
        $payload = $block->getPresetPayload('default');
        $builderData = $block->makeBuilderData($payload);

        return $this->valuesForEditor($block->knobs(), is_array($builderData) ? $builderData : []);
    }

    /**
     * @return array<string, mixed>
     */
    private function exportField(KnobDefinition $definition): array
    {
        return [
            'name' => $definition->getName(),
            'label' => $definition->getLabel(),
            'type' => $definition->getType(),
            'group' => $definition->getGroup(),
            'helperText' => $definition->getHelperText(),
            'required' => $definition->isRequired(),
            'options' => array_map(
                static fn (string $label, string $value): array => ['value' => $value, 'label' => $label],
                $definition->getOptions(),
                array_keys($definition->getOptions()),
            ),
            'fields' => array_map(
                fn (KnobDefinition $field): array => $this->exportField($field),
                $definition->getRepeaterSchema(),
            ),
            'disk' => $definition->getFileDisk(),
            'directory' => $definition->getFileDirectory(),
            'image' => $definition->isImageFile(),
            'itemLabelField' => $definition->getRepeaterItemLabelField(),
            'addActionLabel' => $definition->getRepeaterAddActionLabel(),
            'minItems' => $definition->getMinItems(),
            'maxItems' => $definition->getMaxItems(),
        ];
    }

    private function fileValueForEditor(KnobDefinition $definition, mixed $value): ?array
    {
        if (is_array($value)) {
            $value = collect($value)
                ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
                ->first();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $path = trim($value);
        $disk = $definition->getFileDisk() ?? 'public';

        return [
            'path' => $path,
            'url' => $this->fileUrl($disk, $path),
            'disk' => $disk,
            'name' => basename($path),
            'image' => $definition->isImageFile(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function repeaterValuesForEditor(KnobDefinition $definition, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $item): array => $this->valuesForEditor(
                $definition->getRepeaterSchema(),
                is_array($item) ? $item : [],
            ),
            array_filter($value, 'is_array'),
        ));
    }

    private function fileValueForPersistence(mixed $value): ?string
    {
        if (is_array($value)) {
            $path = $value['path'] ?? null;

            return is_string($path) && trim($path) !== '' ? trim($path) : null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function repeaterValuesForPersistence(KnobDefinition $definition, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $item): array => $this->valuesForPersistence(
                $definition->getRepeaterSchema(),
                is_array($item) ? $item : [],
            ),
            array_filter($value, 'is_array'),
        ));
    }

    private function fileUrl(string $disk, string $path): ?string
    {
        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    private function schemaSlug(AbstractBlockStory $block, string $source): string
    {
        if ($source === 'definition') {
            return str($block->getBlockType())
                ->after('component-')
                ->value();
        }

        return $block->getBlockType();
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, string|null>
     */
    private function extractDataSource(array $defaults): array
    {
        $path = $defaults['payload_path'] ?? $defaults['column_name'] ?? $defaults['widget_key'] ?? $defaults['schema_key'] ?? null;
        $hydration = $defaults['hydration_logic'] ?? $defaults['query_scope'] ?? null;

        return [
            'model' => is_string($defaults['data_source_model'] ?? null) ? $defaults['data_source_model'] : null,
            'path' => is_string($path) ? $path : null,
            'relationship' => is_string($defaults['relationship'] ?? null) && trim((string) $defaults['relationship']) !== ''
                ? (string) $defaults['relationship']
                : null,
            'hydration' => is_string($hydration) ? $hydration : null,
        ];
    }
}

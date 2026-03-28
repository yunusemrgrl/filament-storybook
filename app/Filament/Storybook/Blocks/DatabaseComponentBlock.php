<?php

namespace App\Filament\Storybook\Blocks;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Filament\Storybook\Blocks\Data\ComponentDefinitionBlockData;
use App\Models\ComponentDefinition;

class DatabaseComponentBlock extends AbstractBlockStory
{
    public string $group = 'Components';

    public string $icon = 'heroicon-o-squares-2x2';

    public function __construct(
        private readonly ComponentDefinition $definition,
    ) {
        $this->title = $definition->name;
        $this->description = $definition->description ?? '';
        $this->slug = 'components-'.$definition->handle;
        $this->group = filled($definition->category) ? $definition->category : 'Components';
    }

    public function variants(): array
    {
        return ['default'];
    }

    public function knobs(): array
    {
        $defaults = $this->definition->getDefaultValues();

        return array_map(
            static function ($definition) use ($defaults) {
                $default = $defaults[$definition->getName()] ?? null;

                if ($default !== null) {
                    $definition->default($default);
                }

                return $definition;
            },
            $this->definition->propsCollection()->toKnobDefinitions(),
        );
    }

    public function getBlockType(): string
    {
        return $this->definition->getBlockType();
    }

    public function getSurface(): ComponentSurface
    {
        return $this->definition->getSurface();
    }

    /**
     * @param  array<string, mixed>  $knobs
     * @return array<string, mixed>
     */
    public function makeBlockPayload(array $knobs, string $preset): array
    {
        return [
            'type' => $this->getBlockType(),
            'variant' => $preset,
            'version' => $this->getBlockVersion(),
            'component_definition_id' => $this->definition->getKey(),
            'component_handle' => $this->definition->handle,
            'component_name' => $this->definition->name,
            'view' => $this->definition->view,
            'props' => $this->definition->normalizeProps($knobs),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function resolveBlockData(array $payload): BlockDataContract
    {
        return ComponentDefinitionBlockData::fromDefinition($this->definition, $payload);
    }

    public function getFrontendView(): string
    {
        return $this->definition->view;
    }

    public function getBuilderPreviewView(): ?string
    {
        return 'filament.resources.pages.partials.builder-block-preview';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function makeBuilderData(array $payload): array
    {
        $props = $payload['props'] ?? [];

        if (! is_array($props)) {
            $props = [];
        }

        return $this->definition->getBuilderData($props);
    }

    public function getBuilderItemLabel(?array $state = null): string
    {
        $labelField = $this->definition->getBuilderLabelField();

        if ($labelField && is_string($state[$labelField] ?? null)) {
            $value = trim($state[$labelField]);

            if ($value !== '') {
                return $value;
            }
        }

        return $this->definition->name;
    }

    public function anatomy(): array
    {
        return [
            [
                'title' => 'Definition-backed schema',
                'description' => 'Bu block Filament resource uzerinden kaydedilen prop tanimlarindan uretilir.',
            ],
            [
                'title' => 'Reusable defaults',
                'description' => 'Default values component eklenirken editora hazir state olarak gelir.',
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
[
    'type' => 'component-handle',
    'component_definition_id' => 1,
    'component_handle' => 'hero_banner',
    'view' => 'page-builder.components.hero-banner',
    'props' => [
        'headline' => 'Launch your next campaign faster',
    ],
]
PHP;
    }
}

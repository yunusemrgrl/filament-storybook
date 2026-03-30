<?php

namespace Database\Factories;

use App\ComponentPropType;
use App\ComponentSurface;
use App\Models\ComponentDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ComponentDefinition>
 */
class ComponentDefinitionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::headline($name),
            'handle' => Str::snake($name),
            'surface' => ComponentSurface::Page,
            'category' => 'General',
            'view' => 'page-builder.components.filament-primitive',
            'description' => fake()->sentence(),
            'props' => [
                [
                    'name' => 'payload_path',
                    'label' => 'payloadPath',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Data Source',
                    'required' => true,
                ],
                [
                    'name' => 'data_source_model',
                    'label' => 'dataSourceModel',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Data Source',
                ],
            ],
            'default_values' => [
                'payload_path' => 'payload.path',
                'data_source_model' => 'App\\Models\\User',
            ],
            'is_active' => true,
        ];
    }

    public function heroBanner(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Hero Banner',
            'handle' => 'hero_banner',
            'surface' => ComponentSurface::Page,
            'category' => 'Marketing',
            'view' => 'page-builder.components.hero-banner',
            'description' => 'Hero section with message, CTA, and image.',
            'props' => [
                [
                    'name' => 'headline',
                    'label' => 'Headline',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                    'required' => true,
                ],
                [
                    'name' => 'subheadline',
                    'label' => 'Subheadline',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                ],
                [
                    'name' => 'cta_text',
                    'label' => 'CTA text',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                ],
                [
                    'name' => 'cta_url',
                    'label' => 'CTA URL',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                ],
                [
                    'name' => 'text_align',
                    'label' => 'Text align',
                    'type' => ComponentPropType::Select->value,
                    'group' => 'Design',
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                        ['value' => 'right', 'label' => 'Right'],
                    ],
                ],
                [
                    'name' => 'image',
                    'label' => 'Hero image',
                    'type' => ComponentPropType::File->value,
                    'group' => 'Media',
                    'disk' => 'public',
                    'directory' => 'page-builder/hero-banners',
                    'image' => true,
                ],
                [
                    'name' => 'image_alt',
                    'label' => 'Image alt',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Media',
                ],
            ],
            'default_values' => [
                'headline' => 'Launch your next campaign faster',
                'subheadline' => 'Compose merchant-facing sections from reusable prop definitions.',
                'cta_text' => 'Explore now',
                'cta_url' => '/collections/launch',
                'text_align' => 'left',
                'image_alt' => 'Hero banner visual',
            ],
        ]);
    }

    public function faq(): static
    {
        return $this->state(fn (): array => [
            'name' => 'FAQ',
            'handle' => 'faq',
            'surface' => ComponentSurface::Page,
            'category' => 'Support',
            'view' => 'page-builder.components.faq',
            'description' => 'Frequently asked questions section.',
            'props' => [
                [
                    'name' => 'section_title',
                    'label' => 'Section title',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                    'required' => true,
                ],
                [
                    'name' => 'intro',
                    'label' => 'Intro',
                    'type' => ComponentPropType::Text->value,
                    'group' => 'Content',
                ],
                [
                    'name' => 'items',
                    'label' => 'Items',
                    'type' => ComponentPropType::Repeater->value,
                    'group' => 'Content',
                    'fields' => [
                        [
                            'name' => 'question',
                            'label' => 'Question',
                            'type' => ComponentPropType::Text->value,
                            'required' => true,
                        ],
                        [
                            'name' => 'answer',
                            'label' => 'Answer',
                            'type' => ComponentPropType::Text->value,
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'default_values' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ]);
    }
}

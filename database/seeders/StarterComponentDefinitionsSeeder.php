<?php

namespace Database\Seeders;

use App\ComponentPropType;
use App\ComponentSurface;
use App\Models\ComponentDefinition;
use Illuminate\Database\Seeder;

class StarterComponentDefinitionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ComponentDefinition::query()->updateOrCreate(
            [
                'handle' => 'hero_banner',
                'surface' => ComponentSurface::Page,
            ],
            [
                'name' => 'Hero Banner',
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
                'is_active' => true,
            ],
        );

        ComponentDefinition::query()->updateOrCreate(
            [
                'handle' => 'faq',
                'surface' => ComponentSurface::Page,
            ],
            [
                'name' => 'FAQ',
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
                'is_active' => true,
            ],
        );

        ComponentDefinition::query()->updateOrCreate(
            [
                'handle' => 'main_navigation',
                'surface' => ComponentSurface::Navigation,
            ],
            [
                'name' => 'Main Navigation',
                'category' => 'Navigation',
                'view' => 'page-builder.components.faq',
                'description' => 'Future navigation surface starter definition.',
                'props' => [
                    [
                        'name' => 'label',
                        'label' => 'Label',
                        'type' => ComponentPropType::Text->value,
                        'group' => 'Content',
                        'required' => true,
                    ],
                    [
                        'name' => 'theme',
                        'label' => 'Theme',
                        'type' => ComponentPropType::Select->value,
                        'group' => 'Design',
                        'options' => [
                            ['value' => 'light', 'label' => 'Light'],
                            ['value' => 'dark', 'label' => 'Dark'],
                            ['value' => 'minimal', 'label' => 'Minimal'],
                        ],
                    ],
                    [
                        'name' => 'items',
                        'label' => 'Items',
                        'type' => ComponentPropType::Repeater->value,
                        'group' => 'Content',
                        'fields' => [
                            [
                                'name' => 'label',
                                'label' => 'Label',
                                'type' => ComponentPropType::Text->value,
                                'required' => true,
                            ],
                            [
                                'name' => 'url',
                                'label' => 'URL',
                                'type' => ComponentPropType::Text->value,
                                'required' => true,
                            ],
                        ],
                    ],
                ],
                'default_values' => [
                    'label' => 'Primary navigation',
                    'theme' => 'light',
                    'items' => [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'Catalog', 'url' => '/catalog'],
                    ],
                ],
                'is_active' => true,
            ],
        );

        ComponentDefinition::query()->updateOrCreate(
            [
                'handle' => 'revenue_snapshot',
                'surface' => ComponentSurface::Dashboard,
            ],
            [
                'name' => 'Revenue Snapshot',
                'category' => 'Metrics',
                'view' => 'page-builder.components.hero-banner',
                'description' => 'Future dashboard surface starter definition.',
                'props' => [
                    [
                        'name' => 'title',
                        'label' => 'Title',
                        'type' => ComponentPropType::Text->value,
                        'group' => 'Content',
                        'required' => true,
                    ],
                    [
                        'name' => 'metric',
                        'label' => 'Metric',
                        'type' => ComponentPropType::Text->value,
                        'group' => 'Content',
                        'required' => true,
                    ],
                    [
                        'name' => 'tone',
                        'label' => 'Tone',
                        'type' => ComponentPropType::Select->value,
                        'group' => 'Design',
                        'options' => [
                            ['value' => 'neutral', 'label' => 'Neutral'],
                            ['value' => 'success', 'label' => 'Success'],
                            ['value' => 'warning', 'label' => 'Warning'],
                        ],
                    ],
                ],
                'default_values' => [
                    'title' => 'Gross revenue',
                    'metric' => 'TRY 842,000',
                    'tone' => 'success',
                ],
                'is_active' => true,
            ],
        );
    }
}

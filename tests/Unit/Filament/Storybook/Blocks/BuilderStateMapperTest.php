<?php

use App\Filament\Storybook\Blocks\BlockCollection;
use App\Filament\Storybook\Blocks\BuilderStateMapper;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
});

it('maps builder state into normalized CMS block payloads', function () {
    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $collection = app(BuilderStateMapper::class)->fromBuilderState([
        [
            'type' => $hero->getBlockType(),
            'data' => [
                'headline' => 'Summer launch',
                'subheadline' => 'Campaign pages now share the same payload contract.',
                'cta_text' => 'Shop now',
                'cta_url' => '/summer-launch',
                'image' => ['page-builder/hero-banners/summer-launch.png'],
                'image_alt' => 'Summer launch hero',
                'text_align' => 'left',
            ],
        ],
        [
            'type' => $faq->getBlockType(),
            'data' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);

    expect($collection)->toBeInstanceOf(BlockCollection::class)
        ->and($collection->count())->toBe(2)
        ->and($collection->toArray())->toMatchArray([
            [
                'type' => $hero->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $hero->getKey(),
                'component_handle' => 'hero_banner_component',
                'component_name' => 'Hero Banner Component',
                'view' => 'page-builder.components.hero-banner',
                'props' => [
                    'headline' => 'Summer launch',
                    'subheadline' => 'Campaign pages now share the same payload contract.',
                    'cta_text' => 'Shop now',
                    'cta_url' => '/summer-launch',
                    'text_align' => 'left',
                    'image' => 'page-builder/hero-banners/summer-launch.png',
                    'image_alt' => 'Summer launch hero',
                ],
            ],
            [
                'type' => $faq->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $faq->getKey(),
                'component_handle' => 'faq_component',
                'component_name' => 'FAQ Component',
                'view' => 'page-builder.components.faq',
                'props' => [
                    'section_title' => 'Shipping help',
                    'intro' => 'Everything about delivery and returns.',
                    'items' => [
                        [
                            'question' => 'When do orders ship?',
                            'answer' => 'Orders placed before 16:00 ship the same day.',
                        ],
                    ],
                ],
            ],
        ]);
});

it('maps persisted payloads back into builder state', function () {
    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $builderState = app(BuilderStateMapper::class)->toBuilderState([
        [
            'type' => $hero->getBlockType(),
            'variant' => 'default',
            'version' => 1,
            'component_definition_id' => $hero->getKey(),
            'component_handle' => 'hero_banner_component',
            'component_name' => 'Hero Banner Component',
            'view' => 'page-builder.components.hero-banner',
            'props' => [
                'headline' => 'Summer launch',
                'subheadline' => 'Campaign pages now share the same payload contract.',
                'cta_text' => 'Shop now',
                'cta_url' => '/summer-launch',
                'image' => 'page-builder/hero-banners/summer-launch.png',
                'image_alt' => 'Summer launch hero',
                'text_align' => 'left',
            ],
        ],
        [
            'type' => $faq->getBlockType(),
            'variant' => 'default',
            'version' => 1,
            'component_definition_id' => $faq->getKey(),
            'component_handle' => 'faq_component',
            'component_name' => 'FAQ Component',
            'view' => 'page-builder.components.faq',
            'props' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);

    expect($builderState)->toBe([
        [
            'type' => $hero->getBlockType(),
            'data' => [
                'headline' => 'Summer launch',
                'subheadline' => 'Campaign pages now share the same payload contract.',
                'cta_text' => 'Shop now',
                'cta_url' => '/summer-launch',
                'text_align' => 'left',
                'image' => ['page-builder/hero-banners/summer-launch.png'],
                'image_alt' => 'Summer launch hero',
            ],
        ],
        [
            'type' => $faq->getBlockType(),
            'data' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);
});

it('maps database-defined component builder state into persisted payloads and back', function () {
    $definition = ComponentDefinition::factory()->faq()->create();

    $collection = app(BuilderStateMapper::class)->fromBuilderState([
        [
            'type' => $definition->getBlockType(),
            'data' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);

    expect($collection->toArray())->toBe([
        [
            'type' => $definition->getBlockType(),
            'variant' => 'default',
            'version' => 1,
            'component_definition_id' => $definition->getKey(),
            'component_handle' => 'faq',
            'component_name' => 'FAQ',
            'view' => 'page-builder.components.faq',
            'props' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);

    expect(app(BuilderStateMapper::class)->toBuilderState($collection))->toBe([
        [
            'type' => $definition->getBlockType(),
            'data' => [
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ],
        ],
    ]);
});

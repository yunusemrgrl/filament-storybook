<?php

use App\Filament\Storybook\Blocks\BlockFactory;
use App\Filament\Storybook\Blocks\Data\ComponentDefinitionBlockData;
use App\Filament\Storybook\Blocks\Data\FaqBlockData;
use App\Filament\Storybook\Blocks\Data\HeroBannerBlockData;
use App\Filament\Storybook\Blocks\Data\ProductGridBlockData;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
});

it('resolves a hero banner payload through the block factory', function () {
    $resolved = app(BlockFactory::class)->make([
        'type' => 'hero-banner',
        'variant' => 'default',
        'version' => 1,
        'content' => [
            'headline' => 'Launch faster',
            'subheadline' => 'Ship editor-driven landing pages with typed blocks.',
        ],
        'actions' => [
            'primary' => [
                'text' => 'Explore',
                'url' => '/products',
            ],
        ],
        'media' => [
            'imagePath' => 'page-blocks/hero-banners/launch.png',
            'imageAlt' => 'Launch hero',
        ],
        'design' => [
            'textAlign' => 'center',
            'paddingTop' => 'lg',
            'paddingBottom' => 'lg',
        ],
    ]);

    expect($resolved->story->getBlockType())->toBe('hero-banner')
        ->and($resolved->data)->toBeInstanceOf(HeroBannerBlockData::class)
        ->and($resolved->previewView())->toBe('filament.storybook.blocks.hero-banner')
        ->and($resolved->previewData()['block']->headline)->toBe('Launch faster');
});

it('resolves an FAQ payload through the block factory', function () {
    $resolved = app(BlockFactory::class)->make([
        'type' => 'faq',
        'variant' => 'default',
        'version' => 1,
        'content' => [
            'sectionTitle' => 'Shipping help',
            'introText' => 'Everything about delivery and returns.',
        ],
        'data' => [
            'items' => [
                [
                    'question' => 'When do orders ship?',
                    'answer' => 'Orders placed before 16:00 ship the same day.',
                ],
            ],
        ],
        'design' => [
            'paddingTop' => 'md',
            'paddingBottom' => 'sm',
        ],
    ]);

    expect($resolved->story->getBlockType())->toBe('faq')
        ->and($resolved->data)->toBeInstanceOf(FaqBlockData::class)
        ->and($resolved->previewView())->toBe('filament.storybook.blocks.faq')
        ->and($resolved->previewData()['block']->items)->toHaveCount(1);
});

it('resolves a product grid payload through the block factory', function () {
    $resolved = app(BlockFactory::class)->make([
        'type' => 'product-grid',
        'variant' => 'dense_four_up',
        'version' => 1,
        'content' => [
            'headline' => 'Weekly drop',
            'subheadline' => 'Eight SKUs, tight rhythm.',
        ],
        'data' => [
            'collectionLabel' => 'Weekly drop',
            'itemCount' => 6,
            'showPrices' => true,
        ],
        'design' => [
            'columns' => '4',
            'cardGap' => 'sm',
            'paddingTop' => 'md',
            'paddingBottom' => 'md',
        ],
    ]);

    expect($resolved->story->getBlockType())->toBe('product-grid')
        ->and($resolved->data)->toBeInstanceOf(ProductGridBlockData::class)
        ->and($resolved->previewView())->toBe('filament.storybook.blocks.product-grid')
        ->and($resolved->previewData()['block']->products)->toHaveCount(6);
});

it('throws when an unknown block type is requested', function () {
    expect(fn () => app(BlockFactory::class)->make([
        'type' => 'unknown-block',
    ]))->toThrow(InvalidArgumentException::class, 'Kayitli block bulunamadi: unknown-block');
});

it('resolves a database-defined component payload through the block factory', function () {
    $definition = ComponentDefinition::factory()->heroBanner()->create();

    $resolved = app(BlockFactory::class)->make([
        'type' => $definition->getBlockType(),
        'variant' => 'default',
        'version' => 1,
        'component_definition_id' => $definition->getKey(),
        'component_handle' => $definition->handle,
        'view' => $definition->view,
        'props' => [
            'headline' => 'Scale campaigns faster',
            'subheadline' => 'Now backed by a DB-defined component schema.',
            'cta_text' => 'Explore',
            'cta_url' => '/builder',
            'text_align' => 'center',
        ],
    ]);

    expect($resolved->story->getBlockType())->toBe($definition->getBlockType())
        ->and($resolved->data)->toBeInstanceOf(ComponentDefinitionBlockData::class)
        ->and($resolved->previewView())->toBe('page-builder.components.hero-banner')
        ->and($resolved->previewData()['componentDefinition']->is($definition))->toBeTrue()
        ->and($resolved->previewData()['props']['headline'])->toBe('Scale campaigns faster');
});

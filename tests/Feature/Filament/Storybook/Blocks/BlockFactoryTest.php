<?php

use App\Filament\Storybook\Blocks\BlockFactory;
use App\Filament\Storybook\Blocks\Data\HeroBannerBlockData;
use App\Filament\Storybook\Blocks\Data\ProductGridBlockData;
use App\Filament\Storybook\StoryRegistry;

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

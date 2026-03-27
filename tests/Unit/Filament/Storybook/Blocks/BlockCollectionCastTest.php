<?php

use App\Casts\BlockCollectionCast;
use App\Filament\Storybook\Blocks\BlockCollection;
use App\Models\Page;

it('hydrates a block collection from JSON and serializes it back to arrays', function () {
    $payload = [
        [
            'type' => 'hero-banner',
            'variant' => 'default',
            'version' => 1,
            'content' => [
                'headline' => 'Summer launch',
                'subheadline' => 'Ship editorial campaigns with normalized payloads.',
            ],
            'actions' => [
                'primary' => [
                    'text' => 'Shop now',
                    'url' => '/summer-launch',
                ],
            ],
            'design' => [
                'textAlign' => 'left',
                'paddingTop' => 'xl',
                'paddingBottom' => 'md',
            ],
            'media' => [
                'imagePath' => 'page-blocks/hero-banners/summer-launch.png',
                'imageAlt' => 'Summer launch hero',
            ],
        ],
    ];

    $cast = new BlockCollectionCast;
    $page = new Page;

    $collection = $cast->get($page, 'blocks', json_encode($payload, JSON_THROW_ON_ERROR), []);

    expect($collection)->toBeInstanceOf(BlockCollection::class)
        ->and($collection->count())->toBe(1)
        ->and($collection->toArray())->toBe($payload)
        ->and($cast->set($page, 'blocks', $collection, []))->toBe([
            'blocks' => json_encode($payload, JSON_THROW_ON_ERROR),
        ])
        ->and($cast->serialize($page, 'blocks', $collection, []))->toBe($payload);
});

it('returns an empty block collection for malformed stored values', function () {
    $collection = (new BlockCollectionCast)->get(new Page, 'blocks', '{invalid-json', []);

    expect($collection)->toBeInstanceOf(BlockCollection::class)
        ->and($collection->count())->toBe(0)
        ->and($collection->toArray())->toBe([]);
});

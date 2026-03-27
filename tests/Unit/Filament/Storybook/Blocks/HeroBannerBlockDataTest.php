<?php

use App\Filament\Storybook\Blocks\Data\HeroBannerBlockData;

it('normalizes hero banner block payloads', function () {
    $data = HeroBannerBlockData::fromPayload([
        'variant' => '',
        'version' => '0',
        'content' => [
            'headline' => '  Launch faster  ',
            'subheadline' => '',
        ],
        'actions' => [
            'primary' => [
                'text' => '  Shop now  ',
                'url' => 'collections/new',
            ],
        ],
        'design' => [
            'textAlign' => 'diagonal',
            'paddingTop' => 'mega',
            'paddingBottom' => 'sm',
        ],
    ]);

    expect($data->variant)->toBe('default')
        ->and($data->version)->toBe(1)
        ->and($data->headline)->toBe('Launch faster')
        ->and($data->subheadline)->toContain('page builder')
        ->and($data->primaryCtaText)->toBe('Shop now')
        ->and($data->primaryCtaUrl)->toBe('/collections/new')
        ->and($data->textAlign)->toBe('center')
        ->and($data->paddingTop)->toBe('lg')
        ->and($data->paddingBottom)->toBe('sm')
        ->and($data->wrapperClasses())->toContain('is-pt-lg')
        ->and($data->contentClasses())->toBe('is-align-center');
});

it('serializes hero banner block data back into a stable payload', function () {
    $data = HeroBannerBlockData::fromPayload([
        'variant' => 'left_aligned_large',
        'version' => 2,
        'content' => [
            'headline' => 'Editorial launch',
            'subheadline' => 'A calmer way to present a hero block.',
        ],
        'actions' => [
            'primary' => [
                'text' => 'Read more',
                'url' => '/editorial',
            ],
        ],
        'design' => [
            'textAlign' => 'left',
            'paddingTop' => 'xl',
            'paddingBottom' => 'md',
        ],
    ]);

    expect($data->toArray())->toMatchArray([
        'type' => 'hero-banner',
        'variant' => 'left_aligned_large',
        'version' => 2,
        'content' => [
            'headline' => 'Editorial launch',
            'subheadline' => 'A calmer way to present a hero block.',
        ],
        'actions' => [
            'primary' => [
                'text' => 'Read more',
                'url' => '/editorial',
            ],
        ],
        'design' => [
            'textAlign' => 'left',
            'paddingTop' => 'xl',
            'paddingBottom' => 'md',
        ],
    ]);
});

<?php

use App\Filament\Storybook\Blocks\Data\ProductGridBlockData;

it('normalizes product grid payloads and clamps item count', function () {
    $data = ProductGridBlockData::fromPayload([
        'variant' => 'dense_four_up',
        'version' => '4',
        'content' => [
            'headline' => '  Weekly drop  ',
            'subheadline' => '  Fresh products every Friday.  ',
        ],
        'data' => [
            'collectionLabel' => '  Weekly drop  ',
            'itemCount' => 42,
            'showPrices' => false,
        ],
        'design' => [
            'columns' => '7',
            'cardGap' => 'sm',
            'paddingTop' => 'md',
            'paddingBottom' => 'unknown',
        ],
    ]);

    expect($data->headline)->toBe('Weekly drop')
        ->and($data->subheadline)->toBe('Fresh products every Friday.')
        ->and($data->collectionLabel)->toBe('Weekly drop')
        ->and($data->columns)->toBe('4')
        ->and($data->cardGap)->toBe('sm')
        ->and($data->paddingTop)->toBe('md')
        ->and($data->paddingBottom)->toBe('lg')
        ->and($data->showPrices)->toBeFalse()
        ->and($data->products)->toHaveCount(8)
        ->and($data->gridClasses())->toContain('is-cols-4')
        ->and($data->gridClasses())->toContain('is-gap-sm');
});

it('serializes product grid block data back into a compact payload', function () {
    $data = ProductGridBlockData::fromPayload([
        'variant' => 'two_column_editorial',
        'content' => [
            'headline' => 'Curated essentials',
            'subheadline' => 'Editorial pacing with fewer, bigger cards.',
        ],
        'data' => [
            'collectionLabel' => 'Editors picks',
            'itemCount' => 3,
            'showPrices' => true,
        ],
        'design' => [
            'columns' => '2',
            'cardGap' => 'lg',
            'paddingTop' => 'xl',
            'paddingBottom' => 'xl',
        ],
    ]);

    expect($data->toArray())->toMatchArray([
        'type' => 'product-grid',
        'variant' => 'two_column_editorial',
        'content' => [
            'headline' => 'Curated essentials',
            'subheadline' => 'Editorial pacing with fewer, bigger cards.',
        ],
        'data' => [
            'collectionLabel' => 'Editors picks',
            'itemCount' => 3,
            'showPrices' => true,
        ],
        'design' => [
            'columns' => '2',
            'cardGap' => 'lg',
            'paddingTop' => 'xl',
            'paddingBottom' => 'xl',
        ],
    ]);
});

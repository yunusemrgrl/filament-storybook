<?php

use App\Filament\Storybook\Blocks\Data\FaqBlockData;

it('normalizes FAQ payloads and preserves valid items', function () {
    $data = FaqBlockData::fromPayload([
        'variant' => '',
        'version' => '0',
        'content' => [
            'sectionTitle' => '  Delivery help  ',
            'introText' => '',
        ],
        'data' => [
            'items' => [
                [
                    'question' => '  When do you ship?  ',
                    'answer' => ' Orders placed before 16:00 ship the same day. ',
                ],
                [
                    'question' => '',
                    'answer' => 'This item should be dropped.',
                ],
            ],
        ],
        'design' => [
            'paddingTop' => 'mega',
            'paddingBottom' => 'md',
        ],
    ]);

    expect($data->variant)->toBe('default')
        ->and($data->version)->toBe(1)
        ->and($data->sectionTitle)->toBe('Delivery help')
        ->and($data->introText)->toContain('teslimat')
        ->and($data->paddingTop)->toBe('lg')
        ->and($data->paddingBottom)->toBe('md')
        ->and($data->items)->toBe([
            [
                'question' => 'When do you ship?',
                'answer' => 'Orders placed before 16:00 ship the same day.',
            ],
        ])
        ->and($data->wrapperClasses())->toContain('is-pt-lg')
        ->and($data->hasIntro())->toBeTrue();
});

it('falls back to default items when repeater payload is missing or empty', function () {
    $data = FaqBlockData::fromPayload([
        'content' => [
            'sectionTitle' => 'Support',
        ],
        'data' => [
            'items' => [],
        ],
    ]);

    expect($data->items)->toHaveCount(3)
        ->and($data->items[0]['question'])->toContain('Siparisim');
});

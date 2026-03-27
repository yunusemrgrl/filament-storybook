<?php

use App\Casts\ComponentDefinitionCollectionCast;
use App\ComponentPropDefinitionCollection;
use App\Models\ComponentDefinition;

it('hydrates a component prop definition collection from JSON and serializes it back', function () {
    $payload = [
        [
            'name' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
            'group' => 'Content',
            'required' => true,
        ],
        [
            'name' => 'items',
            'label' => 'Items',
            'type' => 'repeater',
            'fields' => [
                [
                    'name' => 'question',
                    'label' => 'Question',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ],
    ];

    $normalized = [
        [
            'name' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
            'group' => 'Content',
            'helper_text' => null,
            'required' => true,
            'options' => [],
            'fields' => [],
            'disk' => 'public',
            'directory' => 'page-builder/uploads',
            'image' => false,
        ],
        [
            'name' => 'items',
            'label' => 'Items',
            'type' => 'repeater',
            'group' => 'Content',
            'helper_text' => null,
            'required' => false,
            'options' => [],
            'fields' => [
                [
                    'name' => 'question',
                    'label' => 'Question',
                    'type' => 'text',
                    'group' => 'Content',
                    'helper_text' => null,
                    'required' => true,
                    'options' => [],
                    'fields' => [],
                    'disk' => 'public',
                    'directory' => 'page-builder/uploads',
                    'image' => false,
                ],
            ],
            'disk' => 'public',
            'directory' => 'page-builder/uploads',
            'image' => false,
        ],
    ];

    $cast = new ComponentDefinitionCollectionCast;
    $definition = new ComponentDefinition;

    $collection = $cast->get($definition, 'props', json_encode($payload, JSON_THROW_ON_ERROR), []);

    expect($collection)->toBeInstanceOf(ComponentPropDefinitionCollection::class)
        ->and($collection->count())->toBe(2)
        ->and($cast->set($definition, 'props', $collection, []))->toBe([
            'props' => json_encode($normalized, JSON_THROW_ON_ERROR),
        ])
        ->and($cast->serialize($definition, 'props', $collection, []))->toBe($normalized);
});

it('returns an empty component definition collection for malformed stored values', function () {
    $collection = (new ComponentDefinitionCollectionCast)->get(
        new ComponentDefinition,
        'props',
        '{invalid-json',
        [],
    );

    expect($collection)->toBeInstanceOf(ComponentPropDefinitionCollection::class)
        ->and($collection->count())->toBe(0)
        ->and($collection->toArray())->toBe([]);
});

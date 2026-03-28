<?php

use App\ComponentSurface;
use App\Filament\Resources\ComponentDefinitions\Pages\CreateComponentDefinition;
use App\Filament\Resources\ComponentDefinitions\Pages\EditComponentDefinition;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Models\ComponentDefinition;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    BlockRegistry::flush();
    Filament::setCurrentPanel('admin');
});

it('creates a component definition with prop schema and default values', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user);

    Livewire::test(CreateComponentDefinition::class)
        ->fillForm([
            'name' => 'Hero Banner Component',
            'handle' => 'hero_banner_component',
            'surface' => ComponentSurface::Page->value,
            'category' => 'Marketing',
            'view' => 'page-builder.components.hero-banner',
            'is_active' => true,
            'description' => 'Definition-backed hero banner.',
            'props' => [
                [
                    'name' => 'headline',
                    'label' => 'Headline',
                    'type' => 'text',
                    'group' => 'Content',
                    'required' => true,
                ],
                [
                    'name' => 'cta_text',
                    'label' => 'CTA text',
                    'type' => 'text',
                    'group' => 'Content',
                ],
                [
                    'name' => 'text_align',
                    'label' => 'Text align',
                    'type' => 'select',
                    'group' => 'Design',
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                    ],
                ],
            ],
        ])
        ->set('data.default_values', [
            'headline' => 'Launch faster',
            'cta_text' => 'Explore',
            'text_align' => 'left',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $definition = ComponentDefinition::query()
        ->where('handle', 'hero_banner_component')
        ->firstOrFail();

    expect($definition->name)->toBe('Hero Banner Component')
        ->and($definition->getSurface())->toBe(ComponentSurface::Page)
        ->and($definition->propsCollection()->count())->toBe(3)
        ->and($definition->getDefaultValues())->toBe([
            'headline' => 'Launch faster',
            'cta_text' => 'Explore',
            'text_align' => 'left',
        ]);
});

it('hydrates and edits an existing component definition', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $definition = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $this->actingAs($user);

    Livewire::test(EditComponentDefinition::class, ['record' => $definition->getRouteKey()])
        ->assertFormSet([
            'name' => 'Hero Banner Component',
            'handle' => 'hero_banner_component',
            'surface' => ComponentSurface::Page->value,
            'view' => 'page-builder.components.hero-banner',
        ])
        ->fillForm([
            'name' => 'Hero Banner Plus',
            'default_values' => [
                'headline' => 'Scale campaigns faster',
                'subheadline' => 'A meta-builder now controls this schema.',
                'cta_text' => 'See how',
                'cta_url' => '/builder',
                'text_align' => 'center',
                'image_alt' => 'Updated hero visual',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $definition->refresh();

    expect($definition->name)->toBe('Hero Banner Plus')
        ->and($definition->getDefaultValues()['headline'])->toBe('Scale campaigns faster')
        ->and($definition->getDefaultValues()['text_align'])->toBe('center');
});

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
            'name' => 'Technical Text Column',
            'handle' => 'filament.table.text_column.custom',
            'surface' => ComponentSurface::Page->value,
            'category' => 'Tables',
            'view' => 'page-builder.components.filament-primitive',
            'is_active' => true,
            'description' => 'Custom technical text column definition.',
            'props' => [
                [
                    'name' => 'column_name',
                    'label' => 'columnName',
                    'type' => 'text',
                    'group' => 'Data Source',
                    'required' => true,
                ],
                [
                    'name' => 'label',
                    'label' => 'label',
                    'type' => 'text',
                    'group' => 'Appearance',
                ],
                [
                    'name' => 'is_searchable',
                    'label' => 'isSearchable',
                    'type' => 'boolean',
                    'group' => 'Validation',
                ],
            ],
        ])
        ->set('data.default_values', [
            'column_name' => 'email',
            'label' => 'Email',
            'is_searchable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $definition = ComponentDefinition::query()
        ->where('handle', 'filament.table.text_column.custom')
        ->firstOrFail();

    expect($definition->name)->toBe('Technical Text Column')
        ->and($definition->getSurface())->toBe(ComponentSurface::Page)
        ->and($definition->propsCollection()->count())->toBe(3)
        ->and($definition->getDefaultValues())->toBe([
            'column_name' => 'email',
            'label' => 'Email',
            'is_searchable' => true,
        ]);
});

it('hydrates and edits an existing component definition', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $definition = ComponentDefinition::factory()->create([
        'name' => 'Technical Table Widget',
        'handle' => 'filament.widget.table_widget.custom',
        'surface' => ComponentSurface::Page,
        'category' => 'Widgets',
        'view' => 'page-builder.components.filament-primitive',
        'description' => 'Schema-driven table widget definition.',
        'props' => [
            [
                'name' => 'widget_key',
                'label' => 'widgetKey',
                'type' => 'text',
                'group' => 'Data Source',
                'required' => true,
            ],
            [
                'name' => 'query_scope',
                'label' => 'queryScope',
                'type' => 'text',
                'group' => 'Data Source',
            ],
            [
                'name' => 'pagination_size',
                'label' => 'paginationSize',
                'type' => 'number',
                'group' => 'Appearance',
            ],
        ],
        'default_values' => [
            'widget_key' => 'user_registry',
            'query_scope' => 'latest()',
            'pagination_size' => 25,
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(EditComponentDefinition::class, ['record' => $definition->getRouteKey()])
        ->assertFormSet([
            'name' => 'Technical Table Widget',
            'handle' => 'filament.widget.table_widget.custom',
            'surface' => ComponentSurface::Page->value,
            'view' => 'page-builder.components.filament-primitive',
        ])
        ->fillForm([
            'name' => 'Technical Table Widget Plus',
            'default_values' => [
                'widget_key' => 'audit_registry',
                'query_scope' => 'whereNotNull("email_verified_at")',
                'pagination_size' => 50,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $definition->refresh();

    expect($definition->name)->toBe('Technical Table Widget Plus')
        ->and($definition->getDefaultValues()['widget_key'])->toBe('audit_registry')
        ->and($definition->getDefaultValues()['pagination_size'])->toBe(50);
});

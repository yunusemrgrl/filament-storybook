<?php

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Database\Seeders\StarterComponentDefinitionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

it('redirects guests away from page builder routes', function () {
    $page = Page::factory()->create();

    $this->get(route('admin.pages.builder.create'))
        ->assertRedirect('/admin/login');

    $this->get(route('admin.pages.builder.edit', $page))
        ->assertRedirect('/admin/login');
});

it('links the pages list to the custom builder routes', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create([
        'title' => 'User Management',
        'slug' => 'user-management',
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pages.index'))
        ->assertOk()
        ->assertSee(route('admin.pages.builder.create'), false)
        ->assertSee(route('admin.pages.builder.edit', $page), false);
});

it('renders the page builder create shell with page-surface definitions', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    ComponentDefinition::factory()->create([
        'name' => 'Navigation Tree',
        'handle' => 'custom.navigation_tree',
        'surface' => ComponentSurface::Navigation,
    ]);

    $this->actingAs($user)
        ->get(route('admin.pages.builder.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PageBuilder', false)
            ->where('surface', ComponentSurface::Page->value)
            ->where('page.id', null)
            ->where('page.nodes', [])
            ->where('definitions', function ($definitions): bool {
                $types = collect($definitions)->pluck('type');

                return $types->contains('component-filament.layout.grid')
                    && $types->contains('component-filament.form.text_input')
                    && $types->contains('component-filament.widget.table_widget')
                    && ! $types->contains('component-custom.navigation_tree');
            })
            ->where('dataBinding', function ($dataBinding): bool {
                $models = collect($dataBinding['models'] ?? []);
                $userColumns = collect($dataBinding['columnsByModel'][User::class] ?? [])->pluck('name');
                $pageColumns = collect($dataBinding['columnsByModel'][Page::class] ?? [])->pluck('name');

                return $models->pluck('class')->contains(User::class)
                    && $models->pluck('class')->contains(Page::class)
                    && $userColumns->contains('name')
                    && $userColumns->contains('email')
                    && $pageColumns->contains('title');
            })
        );
});

it('hydrates persisted ast nodes on the edit route', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $grid = ComponentDefinition::query()->where('handle', 'filament.layout.grid')->firstOrFail();
    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();
    $tableWidget = ComponentDefinition::query()->where('handle', 'filament.widget.table_widget')->firstOrFail();
    $textColumn = ComponentDefinition::query()->where('handle', 'filament.table.text_column')->firstOrFail();

    $page = Page::factory()->published()->create([
        'title' => 'User Management',
        'slug' => 'user-management',
        'blocks' => [
            [
                'id' => 'grid-node',
                'type' => $grid->getBlockType(),
                'surface' => ComponentSurface::Page->value,
                'label' => 'Registry Grid',
                'props' => ['columns' => 2],
                'children' => [
                    [
                        'id' => 'search-node',
                        'type' => $textInput->getBlockType(),
                        'surface' => ComponentSurface::Page->value,
                        'label' => 'Search users',
                        'props' => [
                            'payload_path' => 'filters.search',
                            'data_source_model' => 'App\\Models\\User',
                            'relationship' => '',
                            'hydration_logic' => 'state',
                            'label' => 'Search users',
                            'placeholder' => 'Search by email',
                            'helper_text' => '',
                            'is_required' => false,
                            'min_length' => 2,
                            'max_length' => 120,
                            'is_searchable' => true,
                            'input_mode' => 'search',
                            'actions' => [],
                        ],
                        'children' => [],
                        'meta' => ['slug' => 'filament.form.text_input', 'source' => 'definition', 'variant' => 'default'],
                    ],
                    [
                        'id' => 'table-node',
                        'type' => $tableWidget->getBlockType(),
                        'surface' => ComponentSurface::Page->value,
                        'label' => 'User Registry',
                        'props' => [
                            'payload_path' => 'widgets.user_registry',
                            'data_source_model' => 'App\\Models\\User',
                            'relationship' => '',
                            'hydration_logic' => 'table.widget',
                            'widget_key' => 'user_registry',
                            'query_scope' => 'latest()',
                            'pagination_size' => 25,
                            'actions' => [],
                        ],
                        'children' => [
                            [
                                'id' => 'column-node',
                                'type' => $textColumn->getBlockType(),
                                'surface' => ComponentSurface::Page->value,
                                'label' => 'Email',
                                'props' => [
                                    'column_path' => 'email',
                                    'data_source_model' => 'App\\Models\\User',
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'hydration_logic' => 'table.column',
                                    'label' => 'Email',
                                    'is_searchable' => true,
                                    'is_sortable' => true,
                                ],
                                'children' => [],
                                'meta' => ['slug' => 'filament.table.text_column', 'source' => 'definition', 'variant' => 'default'],
                            ],
                        ],
                        'meta' => ['slug' => 'filament.widget.table_widget', 'source' => 'definition', 'variant' => 'default'],
                    ],
                ],
                'meta' => ['slug' => 'filament.layout.grid', 'source' => 'definition', 'variant' => 'default'],
            ],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.pages.builder.edit', $page))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('PageBuilder', false)
            ->where('page.id', $page->getKey())
            ->where('page.nodes.0.type', $grid->getBlockType())
            ->where('page.nodes.0.children.0.type', $textInput->getBlockType())
            ->where('page.nodes.0.children.0.props.payload_path', 'filters.search')
            ->where('page.nodes.0.children.1.type', $tableWidget->getBlockType())
            ->where('page.nodes.0.children.1.children.0.type', $textColumn->getBlockType())
            ->where('routes.update', route('admin.pages.builder.update', $page))
            ->where('routes.publicPreview', route('pages.show', $page->slug))
        );
});

it('rejects nodes with undeclared props', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Invalid schema',
            'slug' => 'invalid-schema',
            'status' => PageStatus::Draft->value,
            'nodes' => [
                [
                    'id' => 'text-input-1',
                    'type' => $textInput->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'props' => [
                        'payload_path' => 'filters.user_search',
                        'data_source_model' => 'App\\Models\\User',
                        'relationship' => '',
                        'hydration_logic' => 'state',
                        'label' => 'User search',
                        'placeholder' => 'Search users',
                        'helper_text' => '',
                        'is_required' => false,
                        'validation_rules' => 'nullable|string|max:255',
                        'max_length' => 255,
                        'input_mode' => 'search',
                        'actions' => [],
                        'rogue_flag' => 'not-allowed',
                    ],
                    'children' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'nodes.0.props.rogue_flag',
        ]);
});

it('rejects computed logic expressions that reference missing sibling fields', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Invalid computed schema',
            'slug' => 'invalid-computed-schema',
            'status' => PageStatus::Draft->value,
            'nodes' => [
                [
                    'id' => 'text-input-computed',
                    'type' => $textInput->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'props' => [
                        'data_source_type' => 'state',
                        'payload_path' => 'line_total',
                        'data_source_model' => User::class,
                        'relationship' => '',
                        'relationship_type' => '',
                        'display_column' => '',
                        'value_column' => 'id',
                        'hydration_logic' => 'computed',
                        'label' => 'Line total',
                        'placeholder' => '0',
                        'helper_text' => '',
                        'is_required' => false,
                        'validation_rules' => 'nullable|numeric',
                        'max_length' => 255,
                        'input_mode' => 'numeric',
                        'actions' => [],
                    ],
                    'computed_logic' => [
                        'type' => 'formula',
                        'expression' => '{quantity} * {unit_price}',
                        'precision' => 2,
                    ],
                    'children' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'nodes.0.computed_logic.expression',
        ]);
});

it('rejects nodes bound to models outside the engine whitelist', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Invalid binding schema',
            'slug' => 'invalid-binding-schema',
            'status' => PageStatus::Draft->value,
            'nodes' => [
                [
                    'id' => 'text-input-invalid-model',
                    'type' => $textInput->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'props' => [
                        'data_source_type' => 'model',
                        'payload_path' => 'filters.user_search',
                        'data_source_model' => 'App\\Models\\ComponentDefinition',
                        'relationship' => '',
                        'relationship_type' => '',
                        'display_column' => 'name',
                        'value_column' => 'id',
                        'hydration_logic' => 'state',
                        'label' => 'User search',
                        'placeholder' => 'Search users',
                        'helper_text' => '',
                        'is_required' => false,
                        'validation_rules' => 'nullable|string|max:255',
                        'max_length' => 255,
                        'input_mode' => 'search',
                        'actions' => [],
                    ],
                    'children' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'nodes.0.props.data_source_model',
        ]);
});

it('rejects nodes that reference missing relationships or columns on the selected model', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Invalid relationship schema',
            'slug' => 'invalid-relationship-schema',
            'status' => PageStatus::Draft->value,
            'nodes' => [
                [
                    'id' => 'text-input-invalid-relationship',
                    'type' => $textInput->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'props' => [
                        'data_source_type' => 'relationship',
                        'payload_path' => 'filters.user_search',
                        'data_source_model' => User::class,
                        'relationship' => 'accounts',
                        'relationship_type' => 'hasMany',
                        'display_column' => 'unknown_column',
                        'value_column' => 'id',
                        'hydration_logic' => 'relationship',
                        'label' => 'User search',
                        'placeholder' => 'Search users',
                        'helper_text' => '',
                        'is_required' => false,
                        'validation_rules' => 'nullable|string|max:255',
                        'max_length' => 255,
                        'input_mode' => 'search',
                        'actions' => [],
                    ],
                    'children' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'nodes.0.props.relationship',
            'nodes.0.props.display_column',
        ]);
});

it('stores normalized ast payloads', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $grid = ComponentDefinition::query()->where('handle', 'filament.layout.grid')->firstOrFail();
    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Access Registry',
            'slug' => 'access-registry',
            'status' => PageStatus::Published->value,
            'nodes' => [
                [
                    'id' => 'grid-node',
                    'type' => $grid->getBlockType(),
                    'label' => 'Root Grid',
                    'source' => 'definition',
                    'surface' => ComponentSurface::Page->value,
                    'variant' => 'default',
                    'props' => ['columns' => 2],
                    'children' => [
                        [
                            'id' => 'search-node',
                            'type' => $textInput->getBlockType(),
                            'label' => 'Search users',
                            'source' => 'definition',
                            'surface' => ComponentSurface::Page->value,
                            'variant' => 'default',
                            'props' => [
                                'payload_path' => 'filters.user_search',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => '',
                                'hydration_logic' => 'state',
                                'label' => 'User search',
                                'placeholder' => 'Search users',
                                'helper_text' => 'Maps to registry search state.',
                                'is_required' => true,
                                'min_length' => 3,
                                'max_length' => 120,
                                'is_searchable' => true,
                                'input_mode' => 'search',
                                'actions' => [],
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ])
        ->assertRedirect();

    $page = Page::query()->where('slug', 'access-registry')->firstOrFail();
    $payloads = $page->blocks->toArray();

    expect($page->status)->toBe(PageStatus::Published)
        ->and($page->published_at)->not->toBeNull()
        ->and($payloads)->toHaveCount(1)
        ->and($payloads[0]['type'])->toBe($grid->getBlockType())
        ->and($payloads[0]['children'])->toHaveCount(1)
        ->and($payloads[0]['children'][0]['type'])->toBe($textInput->getBlockType())
        ->and($payloads[0]['children'][0]['props']['payload_path'])->toBe('filters.user_search')
        ->and($payloads[0]['children'][0]['props']['is_required'])->toBeTrue();
});

it('updates normalized ast payloads', function () {
    $user = User::factory()->create();

    $this->seed(StarterComponentDefinitionsSeeder::class);

    $textInput = ComponentDefinition::query()->where('handle', 'filament.form.text_input')->firstOrFail();

    $page = Page::factory()->create([
        'title' => 'Access Registry',
        'slug' => 'access-registry',
        'status' => PageStatus::Draft,
        'blocks' => [
            [
                'id' => 'text-input-1',
                'type' => $textInput->getBlockType(),
                'surface' => ComponentSurface::Page->value,
                'label' => 'Search users',
                'props' => [
                    'payload_path' => 'filters.search',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'hydration_logic' => 'state',
                    'label' => 'Search users',
                    'placeholder' => 'Search users',
                    'helper_text' => '',
                    'is_required' => false,
                    'min_length' => 2,
                    'max_length' => 120,
                    'is_searchable' => true,
                    'input_mode' => 'search',
                    'actions' => [],
                ],
                'children' => [],
                'meta' => ['slug' => 'filament.form.text_input', 'source' => 'definition', 'variant' => 'default'],
            ],
        ],
    ]);

    $this->actingAs($user)
        ->put(route('admin.pages.builder.update', $page), [
            'title' => 'Access Registry Updated',
            'slug' => 'access-registry-updated',
            'status' => PageStatus::Published->value,
            'nodes' => [
                [
                    'id' => 'text-input-1',
                    'type' => $textInput->getBlockType(),
                    'source' => 'definition',
                    'surface' => ComponentSurface::Page->value,
                    'variant' => 'default',
                    'label' => 'Search registry records',
                    'props' => [
                        'payload_path' => 'filters.updated_search',
                        'data_source_model' => 'App\\Models\\User',
                        'relationship' => '',
                        'hydration_logic' => 'state',
                        'label' => 'Search registry records',
                        'placeholder' => 'Search by email',
                        'helper_text' => 'Updated through AST builder.',
                        'is_required' => true,
                        'min_length' => 4,
                        'max_length' => 255,
                        'is_searchable' => true,
                        'input_mode' => 'search',
                        'actions' => [],
                    ],
                    'children' => [],
                ],
            ],
        ])
        ->assertRedirect(route('admin.pages.builder.edit', $page));

    $page->refresh();
    $payloads = $page->blocks->toArray();

    expect($page->title)->toBe('Access Registry Updated')
        ->and($page->slug)->toBe('access-registry-updated')
        ->and($page->status)->toBe(PageStatus::Published)
        ->and($payloads[0]['props']['payload_path'])->toBe('filters.updated_search')
        ->and($payloads[0]['label'])->toBe('Search registry records')
        ->and($payloads[0]['props']['is_required'])->toBeTrue();
});

it('accepts builder asset uploads for authenticated admins', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $definition = ComponentDefinition::factory()->create([
        'name' => 'Engine File Node',
        'handle' => 'engine.file_node',
        'surface' => ComponentSurface::Page,
        'props' => [
            [
                'name' => 'asset',
                'label' => 'Asset',
                'type' => 'file',
                'group' => 'Data Source',
                'disk' => 'public',
                'directory' => 'engine/uploads',
                'image' => true,
            ],
        ],
        'default_values' => [
            'asset' => null,
        ],
    ]);

    $this->actingAs($user)
        ->post(route('admin.pages.builder.upload'), [
            'blockType' => $definition->getBlockType(),
            'fieldName' => 'asset',
            'file' => UploadedFile::fake()->image('schema.png', 1200, 800),
        ])
        ->assertOk()
        ->assertJsonPath('disk', 'public')
        ->assertJsonPath('meta.image', true);

    expect(Storage::disk('public')->allFiles('engine/uploads'))->not->toBeEmpty();
});

it('renders the dashboard builder shell placeholder', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard.builder'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DashboardBuilder', false)
            ->where('widgets.0.title', 'Revenue overview')
            ->where('initialCanvas.0.key', 'revenue-overview')
        );
});

it('renders the navigation builder shell placeholder', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.navigation.builder.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NavigationBuilder', false)
            ->where('navigation.name', 'Admin Sidebar')
            ->where('templates.0.key', 'link')
            ->where('routes.update', route('admin.navigation.builder.update'))
            ->where('initialTree.0.type', 'dropdown')
            ->where('initialTree.0.children.0.label', 'User Management')
        );
});

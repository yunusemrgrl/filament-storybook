<?php

use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\StarterComponentDefinitionsSeeder;

beforeEach(function () {
    static $hasMigrated = false;

    if (! $hasMigrated) {
        $this->artisan('migrate:fresh', ['--force' => true]);
        $hasMigrated = true;
    }

    $this->withVite();
    Page::query()->delete();
    ComponentDefinition::query()->delete();
    User::query()->delete();

    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->seed(StarterComponentDefinitionsSeeder::class);
});

function loginToAdmin(): void
{
    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

    test()->actingAs($user);
}

it('builds and publishes an ast-driven management surface through the react builder shell', function () {
    loginToAdmin();

    visit('/admin/pages/builder/create')
        ->assertPresent('[data-testid="page-builder-shell"]')
        ->assertSee('Schema Registry')
        ->assertSee('Composition Area')
        ->assertSee('Inspector')
        ->fill('[data-testid="page-title-input"]', 'Technical User Management')
        ->fill('[data-testid="page-slug-input"]', 'technical-user-management')
        ->click('[data-testid="page-builder-add-component-filament-layout-grid"]')
        ->assertSee('filament.layout.grid')
        ->click('[data-testid="page-builder-add-component-filament-form-select"]')
        ->assertSee('filament.form.select')
        ->fill('[data-testid="editor-field-payload_path-input"]', 'filters.user_search')
        ->click('[data-testid="editor-field-data_source_model-trigger"]')
        ->click('[data-testid="editor-field-data_source_model-option-app-models-page"]')
        ->click('[data-testid="editor-field-display_column-trigger"]')
        ->click('[data-testid="editor-field-display_column-option-title"]')
        ->click('[data-testid="page-builder-tab-appearance"]')
        ->fill('[data-testid="editor-field-label-input"]', 'User search')
        ->click('[data-testid="page-builder-publish"]');

    visit('/pages/technical-user-management')
        ->assertSee('Technical User Management')
        ->assertSee('filament.layout.grid')
        ->assertSee('filament.form.select')
        ->assertSee('filters.user_search');
});

it('renders the dashboard builder placeholder shell', function () {
    loginToAdmin();

    visit('/admin/dashboard/builder')
        ->assertPresent('[data-testid="dashboard-builder-shell"]')
        ->assertSee('Revenue overview')
        ->click('[data-testid="dashboard-builder-add-widget-fulfillment-health"]')
        ->assertSee('92%');
});

<?php

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;

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

    ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    ComponentDefinition::factory()->create([
        'name' => 'Navigation Menu Component',
        'handle' => 'navigation_menu_component',
        'surface' => ComponentSurface::Navigation,
        'view' => 'page-builder.components.faq',
        'props' => [
            [
                'name' => 'label',
                'label' => 'Label',
                'type' => 'text',
                'group' => 'Content',
            ],
        ],
        'default_values' => [
            'label' => 'Products',
        ],
    ]);
});

function loginToAdmin(): void
{
    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

    test()->actingAs($user);
}

it('builds and publishes a page through the React builder shell', function () {
    loginToAdmin();

    visit('/admin/pages/builder/create')
        ->wait(1)
        ->assertPresent('[data-testid="page-builder-shell"]')
        ->assertSee('Meta CMS Builder')
        ->fill('[data-testid="page-title-input"]', 'Summer launch')
        ->fill('[data-testid="page-slug-input"]', 'summer-launch')
        ->assertDontSee('Navigation Menu Component')
        ->click('[data-testid="page-builder-add-component-hero-banner-component"]')
        ->wait(1)
        ->assertSee('Launch your next campaign faster')
        ->click('[data-testid="page-builder-edit-block"]')
        ->wait(1)
        ->fill('[data-testid="editor-field-headline-input"]', 'Summer launch')
        ->fill('[data-testid="editor-field-subheadline-input"]', 'Launch editor-managed campaigns with a direct render canvas.')
        ->fill('[data-testid="editor-field-cta_text-input"]', 'Shop now')
        ->fill('[data-testid="editor-field-cta_url-input"]', '/summer-launch')
        ->press('Apply changes')
        ->wait(1)
        ->assertSee('Summer launch')
        ->click('[data-testid="page-builder-add-component-faq-component"]')
        ->wait(1)
        ->assertSee('Shipping help')
        ->click('[data-testid="page-builder-edit-block"]')
        ->wait(1)
        ->fill('[data-testid="editor-field-section_title-input"]', 'Shipping help')
        ->fill('[data-testid="editor-field-intro-input"]', 'Everything about delivery and returns.')
        ->fill('[data-testid="editor-field-items-0-question-input"]', 'When do orders ship?')
        ->fill('[data-testid="editor-field-items-0-answer-input"]', 'Orders placed before 16:00 ship the same day.')
        ->press('Apply changes')
        ->wait(1)
        ->assertSee('When do orders ship?')
        ->click('[data-testid="page-builder-publish"]')
        ->wait(1);

    visit('/pages/summer-launch')
        ->wait(1)
        ->assertSee('Summer launch')
        ->assertSee('Shop now')
        ->assertSee('Shipping help')
        ->assertSee('When do orders ship?');
});

it('renders the dashboard builder placeholder shell', function () {
    loginToAdmin();

    visit('/admin/dashboard/builder')
        ->wait(1)
        ->assertPresent('[data-testid="dashboard-builder-shell"]')
        ->assertSee('Dashboard Builder')
        ->assertSee('Revenue overview')
        ->click('[data-testid="dashboard-builder-add-widget-fulfillment-health"]')
        ->assertSee('92%');
});

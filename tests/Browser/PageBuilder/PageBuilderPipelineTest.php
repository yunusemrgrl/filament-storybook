<?php

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\User;

function browserTestImagePath(): string
{
    $path = realpath(base_path('vendor/livewire/livewire/src/Features/SupportFileUploads/browser_test_image.png'));

    expect($path)->not->toBeFalse();

    return $path;
}

beforeEach(function () {
    $this->artisan('migrate:fresh', ['--force' => true]);

    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->actingAs($user);

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

it('builds a page through the custom editor shell and publishes the public page', function () {
    $page = visit('/admin/pages/create')
        ->wait(1)
        ->assertPresent('@page-builder-shell')
        ->assertSee('Meta CMS Editor')
        ->fill('@page-title-input', 'Summer launch')
        ->fill('@page-slug-input', 'summer-launch')
        ->assertDontSee('Navigation Menu Component')
        ->click('@page-builder-add-component-hero-banner-component')
        ->wait(1)
        ->assertSee('Launch your next campaign faster')
        ->click('@page-builder-edit-block')
        ->wait(1)
        ->fill('@editor-field-headline-input', 'Summer launch')
        ->fill('@editor-field-subheadline-input', 'Launch editor-managed campaigns with a direct render canvas.')
        ->fill('@editor-field-cta_text-input', 'Shop now')
        ->fill('@editor-field-cta_url-input', '/summer-launch')
        ->attach('@editor-field-image-file', browserTestImagePath())
        ->wait(1)
        ->press('Apply changes')
        ->wait(1)
        ->assertSee('Summer launch')
        ->click('@page-builder-add-component-faq-component')
        ->wait(1)
        ->assertSee('Shipping help')
        ->click('@page-builder-edit-block')
        ->wait(1)
        ->fill('@editor-field-section_title-input', 'Shipping help')
        ->fill('@editor-field-intro-input', 'Everything about delivery and returns.')
        ->fill('@editor-field-items-item-question-input', 'When do orders ship?')
        ->fill('@editor-field-items-item-answer-input', 'Orders placed before 16:00 ship the same day.')
        ->press('Apply changes')
        ->wait(1)
        ->assertSee('When do orders ship?')
        ->click('@page-builder-publish')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit('/pages/summer-launch')
        ->wait(1)
        ->assertSee('Summer launch')
        ->assertSee('Shop now')
        ->assertSee('Shipping help')
        ->assertSee('When do orders ship?')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('loads the dashboard builder shell placeholder', function () {
    visit('/admin/dashboard-builder')
        ->wait(1)
        ->assertPresent('@dashboard-builder-shell')
        ->assertSee('Dashboard Builder')
        ->assertSee('Revenue overview')
        ->click('@dashboard-builder-add-widget-fulfillment-health')
        ->assertSee('92%')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

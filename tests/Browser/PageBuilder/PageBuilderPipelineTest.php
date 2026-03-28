<?php

use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;

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
});

it('loads database-defined components into the admin page builder with default values', function () {
    $page = visit('/admin/pages/create')
        ->wait(1)
        ->assertSee('Create Page')
        ->assertPresent('@page-preview-panel')
        ->assertPresent('@page-preview-frame')
        ->press('Add block')
        ->click('Hero Banner Component')
        ->assertValue('@builder-field-headline-input', 'Launch your next campaign faster')
        ->assertValue('@builder-field-subheadline-input', 'Compose merchant-facing sections from reusable prop definitions.')
        ->press('Add block')
        ->click('FAQ Component')
        ->assertValue('@builder-field-section_title-input', 'Shipping help')
        ->assertValue('@builder-field-intro-input', 'Everything about delivery and returns.')
        ->assertValue('@builder-field-items-item-question-input', 'When do orders ship?')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $page->fill('@builder-field-headline-input', 'Summer launch')
        ->assertValue('@builder-field-headline-input', 'Summer launch')
        ->assertNoJavaScriptErrors();
});

it('renders a published page that uses database-defined component payloads', function () {
    $heroDefinition = ComponentDefinition::query()->where('handle', 'hero_banner_component')->firstOrFail();
    $faqDefinition = ComponentDefinition::query()->where('handle', 'faq_component')->firstOrFail();

    $page = Page::factory()->published()->create([
        'title' => 'Summer launch',
        'slug' => 'summer-launch',
        'blocks' => [
            [
                'type' => $heroDefinition->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $heroDefinition->getKey(),
                'component_handle' => $heroDefinition->handle,
                'component_name' => $heroDefinition->name,
                'view' => $heroDefinition->view,
                'props' => [
                    'headline' => 'Summer launch',
                    'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                    'cta_text' => 'Shop now',
                    'cta_url' => '/summer-launch',
                    'text_align' => 'left',
                    'image' => null,
                    'image_alt' => 'Summer launch hero',
                ],
            ],
            [
                'type' => $faqDefinition->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $faqDefinition->getKey(),
                'component_handle' => $faqDefinition->handle,
                'component_name' => $faqDefinition->name,
                'view' => $faqDefinition->view,
                'props' => [
                    'section_title' => 'Shipping help',
                    'intro' => 'Everything about delivery and returns.',
                    'items' => [
                        [
                            'question' => 'When do orders ship?',
                            'answer' => 'Orders placed before 16:00 ship the same day.',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    visit("/pages/{$page->slug}")
        ->wait(1)
        ->assertSee('Summer launch')
        ->assertSee('Shop now')
        ->assertSee('Shipping help')
        ->assertSee('When do orders ship?')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

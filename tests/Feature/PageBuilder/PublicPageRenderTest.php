<?php

use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
});

it('does not expose draft pages on the public site', function () {
    $page = Page::factory()->create([
        'slug' => 'draft-page',
        'blocks' => [],
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertNotFound();
});

it('renders published pages with resolved hero and FAQ blocks', function () {
    $page = Page::factory()->published()->create([
        'title' => 'Summer launch',
        'slug' => 'summer-launch',
        'blocks' => [
            [
                'type' => 'hero-banner',
                'variant' => 'default',
                'version' => 1,
                'content' => [
                    'headline' => 'Summer launch',
                    'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                ],
                'actions' => [
                    'primary' => [
                        'text' => 'Shop now',
                        'url' => '/summer-launch',
                    ],
                ],
                'design' => [
                    'textAlign' => 'left',
                    'paddingTop' => 'xl',
                    'paddingBottom' => 'md',
                ],
                'media' => [
                    'imagePath' => 'page-blocks/hero-banners/summer-launch.png',
                    'imageAlt' => 'Summer launch hero',
                ],
            ],
            [
                'type' => 'faq',
                'variant' => 'default',
                'version' => 1,
                'content' => [
                    'sectionTitle' => 'Shipping help',
                    'introText' => 'Everything about delivery and returns.',
                ],
                'data' => [
                    'items' => [
                        [
                            'question' => 'When do orders ship?',
                            'answer' => 'Orders placed before 16:00 ship the same day.',
                        ],
                    ],
                ],
                'design' => [
                    'paddingTop' => 'md',
                    'paddingBottom' => 'sm',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertOk()
        ->assertSee('Summer launch')
        ->assertSee('Launch editor-managed campaigns with a single payload contract.')
        ->assertSee('Shipping help')
        ->assertSee('When do orders ship?')
        ->assertSee('data-testid="hero-banner-block"', false)
        ->assertSee('data-testid="faq-block"', false)
        ->assertSee('css/storybook-blocks.css', false);
});

it('renders published pages with database-defined components', function () {
    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $page = Page::factory()->published()->create([
        'title' => 'Builder launch',
        'slug' => 'builder-launch',
        'blocks' => [
            [
                'type' => $hero->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $hero->getKey(),
                'component_handle' => $hero->handle,
                'component_name' => $hero->name,
                'view' => $hero->view,
                'props' => [
                    'headline' => 'Builder launch',
                    'subheadline' => 'Components are now defined from the admin UI.',
                    'cta_text' => 'See components',
                    'cta_url' => '/admin/component-definitions',
                    'text_align' => 'left',
                    'image' => null,
                    'image_alt' => 'Builder launch visual',
                ],
            ],
            [
                'type' => $faq->getBlockType(),
                'variant' => 'default',
                'version' => 1,
                'component_definition_id' => $faq->getKey(),
                'component_handle' => $faq->handle,
                'component_name' => $faq->name,
                'view' => $faq->view,
                'props' => [
                    'section_title' => 'Need help?',
                    'intro' => 'Frequently asked questions about the builder.',
                    'items' => [
                        [
                            'question' => 'Can editors create pages?',
                            'answer' => 'Yes, page builder now consumes component definitions.',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertOk()
        ->assertSee('Builder launch')
        ->assertSee('Components are now defined from the admin UI.')
        ->assertSee('Need help?')
        ->assertSee('Can editors create pages?')
        ->assertSee('data-testid="dynamic-hero-banner-block"', false)
        ->assertSee('data-testid="dynamic-faq-block"', false);
});

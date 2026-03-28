<?php

use App\ComponentSurface;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
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
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $page = Page::factory()->create([
        'title' => 'Spring launch',
        'slug' => 'spring-launch',
    ]);

    $this->actingAs($user)
        ->get(route('filament.admin.resources.pages.index'))
        ->assertOk()
        ->assertSee(route('admin.pages.builder.create'), false)
        ->assertSee(route('admin.pages.builder.edit', $page), false);
});

it('renders the page builder create shell with only page-surface definitions in the palette', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Page Component',
        'handle' => 'hero_banner_page_component',
        'surface' => ComponentSurface::Page,
    ]);

    ComponentDefinition::factory()->create([
        'name' => 'Navigation Menu Component',
        'handle' => 'navigation_menu_component',
        'surface' => ComponentSurface::Navigation,
    ]);

    ComponentDefinition::factory()->create([
        'name' => 'Dashboard Metric Component',
        'handle' => 'dashboard_metric_component',
        'surface' => ComponentSurface::Dashboard,
    ]);

    $this->actingAs($user)
        ->get(route('admin.pages.builder.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PageBuilder', false)
            ->where('surface', ComponentSurface::Page->value)
            ->where('page.id', null)
            ->where('page.blocks', [])
            ->where('availableBlocks', function ($blocks): bool {
                $types = collect($blocks)->pluck('type');
                $titles = collect($blocks)->pluck('title');

                return $types->contains('hero-banner')
                    && $types->contains('component-hero_banner_page_component')
                    && ! $types->contains('component-navigation_menu_component')
                    && ! $types->contains('component-dashboard_metric_component')
                    && $titles->contains('Hero Banner Page Component')
                    && ! $titles->contains('Navigation Menu Component')
                    && ! $titles->contains('Dashboard Metric Component');
            })
            ->where('routes.store', route('admin.pages.builder.store'))
            ->where('routes.index', route('filament.admin.resources.pages.index'))
        );
});

it('hydrates persisted editor blocks on the edit route', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $page = Page::factory()->published()->create([
        'title' => 'Spring launch',
        'slug' => 'spring-launch',
        'blocks' => [
            $hero->toDatabaseBlock()->makeBlockPayload([
                'headline' => 'Spring launch',
                'subheadline' => 'Compose campaigns from admin-defined blocks.',
                'cta_text' => 'Explore now',
                'cta_url' => '/spring-launch',
                'text_align' => 'left',
                'image' => 'page-builder/hero-banners/spring-launch.png',
                'image_alt' => 'Spring launch hero',
            ], 'default'),
            $faq->toDatabaseBlock()->makeBlockPayload([
                'section_title' => 'Need help?',
                'intro' => 'Answers for the launch campaign.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ], 'default'),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.pages.builder.edit', $page))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('PageBuilder', false)
            ->where('page.id', $page->getKey())
            ->where('page.title', 'Spring launch')
            ->where('page.blocks.0.type', $hero->getBlockType())
            ->where('page.blocks.0.data.headline', 'Spring launch')
            ->where('page.blocks.0.data.image.path', 'page-builder/hero-banners/spring-launch.png')
            ->where('page.blocks.1.type', $faq->getBlockType())
            ->where('page.blocks.1.data.items.0.question', 'When do orders ship?')
            ->where('routes.update', route('admin.pages.builder.update', $page))
            ->where('routes.publicPreview', route('pages.show', $page->slug))
        );
});

it('stores normalized page builder payloads', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $this->actingAs($user)
        ->post(route('admin.pages.builder.store'), [
            'title' => 'Summer launch',
            'slug' => 'summer-launch',
            'status' => PageStatus::Published->value,
            'blocks' => [
                [
                    'id' => 'hero-1',
                    'type' => $hero->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'data' => [
                        'headline' => 'Summer launch',
                        'subheadline' => 'Launch editor-managed campaigns with a direct render canvas.',
                        'cta_text' => 'Shop now',
                        'cta_url' => '/summer-launch',
                        'text_align' => 'left',
                        'image' => [
                            'path' => 'page-builder/hero-banners/summer-launch.png',
                            'url' => 'http://localhost/storage/page-builder/hero-banners/summer-launch.png',
                            'disk' => 'public',
                            'name' => 'summer-launch.png',
                            'image' => true,
                        ],
                        'image_alt' => 'Summer launch hero',
                    ],
                ],
                [
                    'id' => 'faq-1',
                    'type' => $faq->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'data' => [
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
        ])
        ->assertRedirect();

    $page = Page::query()->where('slug', 'summer-launch')->firstOrFail();
    $payloads = $page->blocks->toArray();

    expect($page->status)->toBe(PageStatus::Published)
        ->and($page->published_at)->not->toBeNull()
        ->and($payloads)->toHaveCount(2)
        ->and($payloads[0]['type'])->toBe($hero->getBlockType())
        ->and($payloads[0]['props']['image'])->toBe('page-builder/hero-banners/summer-launch.png')
        ->and($payloads[1]['type'])->toBe($faq->getBlockType())
        ->and($payloads[1]['props']['items'])->toHaveCount(1);
});

it('updates normalized page builder payloads', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $page = Page::factory()->create([
        'title' => 'Spring launch',
        'slug' => 'spring-launch',
        'status' => PageStatus::Draft,
        'blocks' => [
            $hero->toDatabaseBlock()->makeBlockPayload([
                'headline' => 'Spring launch',
                'subheadline' => 'First version.',
                'cta_text' => 'Explore',
                'cta_url' => '/spring-launch',
                'text_align' => 'left',
                'image' => null,
                'image_alt' => 'Spring launch hero',
            ], 'default'),
        ],
    ]);

    $this->actingAs($user)
        ->put(route('admin.pages.builder.update', $page), [
            'title' => 'Spring launch updated',
            'slug' => 'spring-launch-updated',
            'status' => PageStatus::Published->value,
            'blocks' => [
                [
                    'id' => 'hero-1',
                    'type' => $hero->getBlockType(),
                    'source' => 'definition',
                    'variant' => 'default',
                    'data' => [
                        'headline' => 'Spring launch updated',
                        'subheadline' => 'Updated through the React builder.',
                        'cta_text' => 'See more',
                        'cta_url' => '/spring-launch-updated',
                        'text_align' => 'center',
                        'image' => null,
                        'image_alt' => 'Updated launch hero',
                    ],
                ],
            ],
        ])
        ->assertRedirect(route('admin.pages.builder.edit', $page));

    $page->refresh();
    $payloads = $page->blocks->toArray();

    expect($page->title)->toBe('Spring launch updated')
        ->and($page->slug)->toBe('spring-launch-updated')
        ->and($page->status)->toBe(PageStatus::Published)
        ->and($payloads[0]['props']['headline'])->toBe('Spring launch updated')
        ->and($payloads[0]['props']['text_align'])->toBe('center');
});

it('accepts builder asset uploads for authenticated admins', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Component',
        'handle' => 'hero_banner_component',
    ]);

    $this->actingAs($user)
        ->post(route('admin.pages.builder.upload'), [
            'blockType' => $hero->getBlockType(),
            'fieldName' => 'image',
            'file' => UploadedFile::fake()->image('summer-launch.png', 1600, 900),
        ])
        ->assertOk()
        ->assertJsonPath('disk', 'public')
        ->assertJsonPath('meta.image', true)
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('meta.name', 'summer-launch.png')
            ->whereType('path', 'string')
            ->whereType('url', 'string')
            ->etc()
        );

    expect(Storage::disk('public')->allFiles('page-builder/hero-banners'))->not->toBeEmpty();
});

it('renders the dashboard builder shell placeholder', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard.builder'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DashboardBuilder', false)
            ->where('widgets.0.title', 'Revenue overview')
            ->where('initialCanvas.0.key', 'revenue-overview')
        );
});

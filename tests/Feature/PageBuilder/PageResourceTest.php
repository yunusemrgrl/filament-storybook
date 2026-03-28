<?php

use App\ComponentSurface;
use App\Filament\Pages\DashboardBuilder;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
    Filament::setCurrentPanel('admin');
});

it('renders the custom page builder shell on create and edit routes', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $page = Page::factory()->create([
        'title' => 'Draft launch',
        'slug' => 'draft-launch',
        'blocks' => [],
    ]);

    $this->actingAs($user);

    $this->get('/admin/pages/create')
        ->assertOk()
        ->assertSee('data-testid="page-builder-shell"', false)
        ->assertSee('Meta CMS Editor');

    $this->get("/admin/pages/{$page->getRouteKey()}/edit")
        ->assertOk()
        ->assertSee('data-testid="page-builder-shell"', false)
        ->assertSee('Draft launch');
});

it('shows only page-surface component definitions in the builder palette', function () {
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

    ComponentDefinition::factory()->create([
        'name' => 'Dashboard Metric Component',
        'handle' => 'dashboard_metric_component',
        'surface' => ComponentSurface::Dashboard,
        'view' => 'page-builder.components.hero-banner',
        'props' => [
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'group' => 'Content',
            ],
        ],
        'default_values' => [
            'title' => 'Revenue',
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(CreatePage::class)
        ->assertSee('Hero Banner Page Component')
        ->assertDontSee('Navigation Menu Component')
        ->assertDontSee('Dashboard Metric Component');
});

it('creates an admin page from normalized editor blocks', function () {
    Storage::fake('public');
    Storage::disk('public')->put('page-builder/hero-banners/summer-launch.png', 'existing hero');

    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Preview Component',
        'handle' => 'hero_banner_preview_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $this->actingAs($user);

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Summer launch',
            'slug' => 'summer-launch',
            'status' => PageStatus::Published->value,
        ])
        ->set('editorBlocks', [
            [
                'uuid' => 'hero-1',
                ...$hero->toDatabaseBlock()->makeBlockPayload([
                    'headline' => 'Summer launch',
                    'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                    'cta_text' => 'Shop now',
                    'cta_url' => '/summer-launch',
                    'image' => 'page-builder/hero-banners/summer-launch.png',
                    'image_alt' => 'Summer launch hero',
                    'text_align' => 'left',
                ], 'default'),
            ],
            [
                'uuid' => 'faq-1',
                ...$faq->toDatabaseBlock()->makeBlockPayload([
                    'section_title' => 'Shipping help',
                    'intro' => 'Everything about delivery and returns.',
                    'items' => [
                        [
                            'question' => 'When do orders ship?',
                            'answer' => 'Orders placed before 16:00 ship the same day.',
                        ],
                        [
                            'question' => 'What is your return window?',
                            'answer' => 'You can start a return within 14 days.',
                        ],
                    ],
                    'paddingTop' => 'md',
                    'paddingBottom' => 'sm',
                ], 'default'),
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()->where('slug', 'summer-launch')->firstOrFail();
    $payloads = $page->blocks->toArray();

    expect($page->status)->toBe(PageStatus::Published)
        ->and($page->published_at)->not->toBeNull()
        ->and($payloads)->toHaveCount(2)
        ->and($payloads[0]['type'])->toBe($hero->getBlockType())
        ->and($payloads[0]['props']['image'])->toBe('page-builder/hero-banners/summer-launch.png')
        ->and($payloads[1]['type'])->toBe($faq->getBlockType())
        ->and($payloads[1]['props']['items'])->toHaveCount(2);
});

it('hydrates persisted blocks back into the editor state and saves edits', function () {
    Storage::fake('public');
    Storage::disk('public')->put('page-builder/hero-banners/summer-launch.png', 'existing hero');

    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Preview Component',
        'handle' => 'hero_banner_preview_component',
    ]);

    $faq = ComponentDefinition::factory()->faq()->create([
        'name' => 'FAQ Component',
        'handle' => 'faq_component',
    ]);

    $page = Page::factory()->published()->create([
        'title' => 'Summer launch',
        'slug' => 'summer-launch',
        'blocks' => [
            $hero->toDatabaseBlock()->makeBlockPayload([
                'headline' => 'Summer launch',
                'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                'cta_text' => 'Shop now',
                'cta_url' => '/summer-launch',
                'image' => 'page-builder/hero-banners/summer-launch.png',
                'image_alt' => 'Summer launch hero',
                'text_align' => 'left',
            ], 'default'),
            $faq->toDatabaseBlock()->makeBlockPayload([
                'section_title' => 'Shipping help',
                'intro' => 'Everything about delivery and returns.',
                'items' => [
                    [
                        'question' => 'When do orders ship?',
                        'answer' => 'Orders placed before 16:00 ship the same day.',
                    ],
                ],
            ], 'default'),
        ],
    ]);

    $this->actingAs($user);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormSet([
            'title' => 'Summer launch',
            'slug' => 'summer-launch',
            'status' => PageStatus::Published->value,
        ]);

    $editorBlocks = array_values($component->get('editorBlocks'));

    expect($editorBlocks)->toHaveCount(2)
        ->and($editorBlocks[0]['props']['headline'])->toBe('Summer launch')
        ->and($editorBlocks[0]['props']['image'])->toBe('page-builder/hero-banners/summer-launch.png')
        ->and($editorBlocks[1]['props']['section_title'])->toBe('Shipping help');

    $component
        ->fillForm([
            'title' => 'Summer launch updated',
            'slug' => 'summer-launch-updated',
        ])
        ->set('editorBlocks', [
            [
                'uuid' => $editorBlocks[0]['uuid'],
                ...$hero->toDatabaseBlock()->makeBlockPayload([
                    'headline' => 'Summer launch updated',
                    'subheadline' => 'Now backed by a real editor shell.',
                    'cta_text' => 'Explore',
                    'cta_url' => '/summer-launch-updated',
                    'image' => 'page-builder/hero-banners/summer-launch.png',
                    'image_alt' => 'Updated hero',
                    'text_align' => 'center',
                ], 'default'),
            ],
            [
                'uuid' => $editorBlocks[1]['uuid'],
                ...$faq->toDatabaseBlock()->makeBlockPayload([
                    'section_title' => 'Returns help',
                    'intro' => 'Updated FAQs for the campaign.',
                    'items' => [
                        [
                            'question' => 'What is your return window?',
                            'answer' => 'Returns can be started within 14 days.',
                        ],
                    ],
                ], 'default'),
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();
    $payloads = $page->blocks->toArray();

    expect($page->title)->toBe('Summer launch updated')
        ->and($page->slug)->toBe('summer-launch-updated')
        ->and($payloads[0]['props']['headline'])->toBe('Summer launch updated')
        ->and($payloads[1]['props']['section_title'])->toBe('Returns help');
});

it('syncs unsaved editor state into the preview session', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Route Preview Component',
        'handle' => 'hero_banner_route_preview_component',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(CreatePage::class)
        ->set('data.title', 'Preview launch')
        ->set('data.slug', 'preview-launch')
        ->set('data.status', PageStatus::Draft->value)
        ->set('editorBlocks', [
            [
                'uuid' => 'hero-preview',
                ...$hero->toDatabaseBlock()->makeBlockPayload([
                    'headline' => 'Preview launch',
                    'subheadline' => 'The admin editor now renders an unsaved live preview.',
                    'cta_text' => 'Open preview',
                    'cta_url' => '/preview-launch',
                    'image' => null,
                    'image_alt' => 'Preview launch hero',
                    'text_align' => 'center',
                ], 'default'),
            ],
        ]);

    $previewToken = $component->get('previewToken');
    $previewPayload = session()->get("page-builder.preview.{$previewToken}");

    expect($previewToken)->not->toBe('')
        ->and($previewPayload)->toBeArray()
        ->and($previewPayload['title'])->toBe('Preview launch')
        ->and($previewPayload['slug'])->toBe('preview-launch')
        ->and($previewPayload['blocks'])->toHaveCount(1)
        ->and($previewPayload['blocks'][0]['props']['headline'])->toBe('Preview launch');
});

it('renders the admin preview route from session-backed editor payloads', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $hero = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Route Preview Component',
        'handle' => 'hero_banner_route_preview_component',
    ]);

    $this->actingAs($user);

    $token = 'preview-token';

    session()->put("page-builder.preview.{$token}", [
        'title' => 'Preview launch',
        'slug' => 'preview-launch',
        'status' => PageStatus::Draft->value,
        'blocks' => [
            $hero->toDatabaseBlock()->makeBlockPayload([
                'headline' => 'Preview launch',
                'subheadline' => 'The admin editor now renders an unsaved live preview.',
                'cta_text' => 'Open preview',
                'cta_url' => '/preview-launch',
                'image' => null,
                'image_alt' => 'Preview launch hero',
                'text_align' => 'center',
            ], 'default'),
        ],
    ]);

    $this->get(route('admin.pages.preview', ['token' => $token]))
        ->assertOk()
        ->assertSee('Preview launch')
        ->assertSee('The admin editor now renders an unsaved live preview.')
        ->assertSee('data-testid="dynamic-hero-banner-block"', false);
});

it('renders the dashboard builder shell placeholder for authenticated admins', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user);

    $this->get(DashboardBuilder::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('data-testid="dashboard-builder-shell"', false)
        ->assertSee('Dashboard Builder')
        ->assertSee('Revenue overview');
});

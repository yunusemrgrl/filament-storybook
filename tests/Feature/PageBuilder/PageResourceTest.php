<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
    Filament::setCurrentPanel('admin');
});

it('creates an admin page and persists normalized blocks', function () {
    Storage::fake('public');

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

    $this->actingAs($user);

    $upload = UploadedFile::fake()->image('hero-banner.png', 1600, 900);

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Summer launch',
            'slug' => 'summer-launch',
            'status' => PageStatus::Published->value,
            'builderBlocks' => [
                [
                    'type' => $hero->getBlockType(),
                    'data' => [
                        'headline' => 'Summer launch',
                        'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                        'cta_text' => 'Shop now',
                        'cta_url' => 'summer-launch',
                        'image' => [$upload],
                        'image_alt' => 'Summer launch hero',
                        'text_align' => 'left',
                    ],
                ],
                [
                    'type' => $faq->getBlockType(),
                    'data' => [
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
                    ],
                ],
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
        ->and($payloads[0]['props']['cta_url'])->toBe('summer-launch')
        ->and($payloads[0]['props']['image'])->toStartWith('page-builder/hero-banners/')
        ->and($payloads[1]['type'])->toBe($faq->getBlockType())
        ->and($payloads[1]['props']['items'])->toHaveCount(2);

    Storage::disk('public')->assertExists($payloads[0]['props']['image']);
});

it('hydrates persisted blocks back into the admin form and saves edits', function () {
    Storage::fake('public');
    Storage::disk('public')->put('page-builder/hero-banners/summer-launch.png', 'existing hero');

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
        'title' => 'Summer launch',
        'slug' => 'summer-launch',
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
                    'headline' => 'Summer launch',
                    'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                    'cta_text' => 'Shop now',
                    'cta_url' => '/summer-launch',
                    'image' => 'page-builder/hero-banners/summer-launch.png',
                    'image_alt' => 'Summer launch hero',
                    'text_align' => 'left',
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

    $this->actingAs($user);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormSet([
            'title' => 'Summer launch',
            'slug' => 'summer-launch',
            'status' => PageStatus::Published->value,
        ]);

    $builderBlocks = array_values($component->get('data.builderBlocks'));
    $builderBlocks[0]['data']['image'] = array_values($builderBlocks[0]['data']['image']);
    $builderBlocks[1]['data']['items'] = array_values($builderBlocks[1]['data']['items']);

    expect($builderBlocks)->toMatchArray([
        [
            'type' => $hero->getBlockType(),
            'data' => [
                'headline' => 'Summer launch',
                'subheadline' => 'Launch editor-managed campaigns with a single payload contract.',
                'cta_text' => 'Shop now',
                'cta_url' => '/summer-launch',
                'image' => ['page-builder/hero-banners/summer-launch.png'],
                'image_alt' => 'Summer launch hero',
                'text_align' => 'left',
            ],
        ],
        [
            'type' => $faq->getBlockType(),
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
    ]);

    $component
        ->fillForm([
            'title' => 'Summer launch updated',
            'builderBlocks' => [
                [
                    'type' => $hero->getBlockType(),
                    'data' => [
                        'headline' => 'Summer launch updated',
                        'subheadline' => 'Now backed by a real Filament builder pipeline.',
                        'cta_text' => 'Explore',
                        'cta_url' => '/summer-launch-updated',
                        'image' => ['page-builder/hero-banners/summer-launch.png'],
                        'image_alt' => 'Updated hero',
                        'text_align' => 'center',
                    ],
                ],
                [
                    'type' => $faq->getBlockType(),
                    'data' => [
                        'section_title' => 'Returns help',
                        'intro' => 'Updated FAQs for the campaign.',
                        'items' => [
                            [
                                'question' => 'What is your return window?',
                                'answer' => 'Returns can be started within 14 days.',
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();
    $payloads = $page->blocks->toArray();

    expect($page->title)->toBe('Summer launch updated')
        ->and($payloads[0]['props']['headline'])->toBe('Summer launch updated')
        ->and($payloads[0]['props']['cta_url'])->toBe('/summer-launch-updated')
        ->and($payloads[1]['props']['section_title'])->toBe('Returns help')
        ->and($payloads[1]['props']['items'][0]['question'])->toBe('What is your return window?');
});

it('creates an admin page from database-defined components', function () {
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

    $this->actingAs($user);

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Builder launch',
            'slug' => 'builder-launch',
            'status' => PageStatus::Published->value,
            'builderBlocks' => [
                [
                    'type' => $hero->getBlockType(),
                    'data' => [
                        'headline' => 'Builder launch',
                        'subheadline' => 'Components are now defined from the admin UI.',
                        'cta_text' => 'See components',
                        'cta_url' => '/admin/component-definitions',
                        'text_align' => 'left',
                        'image' => [],
                        'image_alt' => 'Builder launch visual',
                    ],
                ],
                [
                    'type' => $faq->getBlockType(),
                    'data' => [
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
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()->where('slug', 'builder-launch')->firstOrFail();
    $payloads = $page->blocks->toArray();

    expect($payloads[0]['type'])->toBe($hero->getBlockType())
        ->and($payloads[0]['component_definition_id'])->toBe($hero->getKey())
        ->and($payloads[0]['props']['headline'])->toBe('Builder launch')
        ->and($payloads[1]['type'])->toBe($faq->getBlockType())
        ->and($payloads[1]['props']['items'][0]['question'])->toBe('Can editors create pages?');
});

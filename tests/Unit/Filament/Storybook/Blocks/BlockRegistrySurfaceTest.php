<?php

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
});

it('filters database component definitions by surface', function () {
    $pageDefinition = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Page Component',
        'handle' => 'hero_banner_page_component',
        'surface' => ComponentSurface::Page,
    ]);

    $navigationDefinition = ComponentDefinition::factory()->create([
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

    $dashboardDefinition = ComponentDefinition::factory()->create([
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

    expect(BlockRegistry::forSurface(ComponentSurface::Page))
        ->toHaveKey($pageDefinition->getBlockType())
        ->not->toHaveKey($navigationDefinition->getBlockType())
        ->not->toHaveKey($dashboardDefinition->getBlockType());

    expect(BlockRegistry::forSurface(ComponentSurface::Navigation))
        ->toHaveKey($navigationDefinition->getBlockType())
        ->not->toHaveKey($pageDefinition->getBlockType());

    expect(BlockRegistry::forSurface(ComponentSurface::Dashboard))
        ->toHaveKey($dashboardDefinition->getBlockType())
        ->not->toHaveKey($pageDefinition->getBlockType());
});

it('exposes only page-surface database definitions inside the storybook lab', function () {
    $pageDefinition = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Page Component',
        'handle' => 'hero_banner_page_component',
        'surface' => ComponentSurface::Page,
    ]);

    $navigationDefinition = ComponentDefinition::factory()->create([
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

    expect(StoryRegistry::findBySlug('components-'.$pageDefinition->handle))->not->toBeNull()
        ->and(StoryRegistry::findBySlug('components-'.$navigationDefinition->handle))->toBeNull();
});

<?php

use App\ComponentSurface;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    StoryRegistry::flush();
});

it('renders the hero banner overview page', function () {
    $response = $this->get(storybookPageUrl('page-blocks-hero-banner'));

    $response->assertSuccessful()
        ->assertSeeText('Hero Banner')
        ->assertSeeText('Default hero')
        ->assertSeeText('Centered compact');
});

it('renders the FAQ block overview page', function () {
    $response = $this->get(storybookPageUrl('page-blocks-faq'));

    $response->assertSuccessful()
        ->assertSeeText('FAQ')
        ->assertSeeText('Default FAQ');
});

it('renders database-defined page surface components in the lab and excludes non-page definitions', function () {
    $pageDefinition = ComponentDefinition::factory()->heroBanner()->create([
        'name' => 'Hero Banner Lab Component',
        'handle' => 'hero_banner_lab_component',
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

    $response = $this->get(storybookPageUrl('components-'.$pageDefinition->handle));

    $response->assertSuccessful()
        ->assertSeeText('Hero Banner Lab Component')
        ->assertDontSeeText('Navigation Menu Component');
});

it('renders the product grid playground for a selected preset', function () {
    $response = $this->get(storybookPageUrl('page-blocks-product-grid', 'dense_four_up'));

    $response->assertSuccessful()
        ->assertSeeText('Dense four-up')
        ->assertSeeText('Weekly drop')
        ->assertSeeText('Knobs');
});

dataset('primitive story slugs', [
    ['forms-select', 'Select'],
    ['forms-fileupload', 'FileUpload'],
    ['forms-repeater', 'Repeater'],
]);

it('renders primitive form stories for the CMS MVP', function (string $slug, string $title) {
    $response = $this->get(storybookPageUrl($slug, 'default'));

    $response->assertSuccessful()
        ->assertSeeText($title)
        ->assertSeeText('Knobs');
})->with('primitive story slugs');

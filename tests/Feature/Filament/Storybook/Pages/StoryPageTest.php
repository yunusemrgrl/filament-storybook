<?php

use App\Filament\Storybook\StoryRegistry;

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

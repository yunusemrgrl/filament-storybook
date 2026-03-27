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

it('renders the product grid playground for a selected preset', function () {
    $response = $this->get(storybookPageUrl('page-blocks-product-grid', 'dense_four_up'));

    $response->assertSuccessful()
        ->assertSeeText('Dense four-up')
        ->assertSeeText('Weekly drop')
        ->assertSeeText('Knobs');
});

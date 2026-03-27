<?php

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\StoryRegistry;

beforeEach(function () {
    StoryRegistry::flush();
});

it('renders the hero banner overview page', function () {
    $response = $this->get(storybookPageUrl('page-blocks-hero-banner'));

    $response->assertSuccessful()
        ->assertViewHas('renderType', 'block')
        ->assertViewHas('story', fn ($story) => $story instanceof AbstractBlockStory && $story->title === 'Hero Banner')
        ->assertSee('Hero Banner')
        ->assertSee('Default hero')
        ->assertSee('Centered compact');
});

it('renders the product grid playground for a selected preset', function () {
    $response = $this->get(storybookPageUrl('page-blocks-product-grid', 'dense_four_up'));

    $response->assertSuccessful()
        ->assertViewHas('renderType', 'block')
        ->assertViewHas('activePreset', 'dense_four_up')
        ->assertSee('Dense four-up')
        ->assertSee('Weekly drop')
        ->assertSee('Knobs');
});

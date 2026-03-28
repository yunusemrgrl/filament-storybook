<?php

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('seeds a starter meta cms workspace', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);

    expect(
        User::query()
            ->whereIn('email', ['admin@example.com', 'editor@example.com', 'test@example.com'])
            ->count()
    )->toBe(3);

    expect(
        ComponentDefinition::query()
            ->forSurface(ComponentSurface::Page)
            ->pluck('handle')
            ->all()
    )->toContain('hero_banner', 'faq');

    expect(
        ComponentDefinition::query()
            ->forSurface(ComponentSurface::Navigation)
            ->pluck('handle')
            ->all()
    )->toContain('main_navigation');

    expect(
        ComponentDefinition::query()
            ->forSurface(ComponentSurface::Dashboard)
            ->pluck('handle')
            ->all()
    )->toContain('revenue_snapshot');

    $homePage = Page::query()->where('slug', 'home')->first();
    $draftPage = Page::query()->where('slug', 'spring-launch')->first();

    expect($homePage)->not->toBeNull();
    expect($draftPage)->not->toBeNull();
    expect($homePage?->status)->toBe(PageStatus::Published);
    expect($draftPage?->status)->toBe(PageStatus::Draft);
    expect($homePage?->blocks->count())->toBe(2);
    expect($draftPage?->blocks->count())->toBe(1);

    Storage::disk('public')->assertExists('page-builder/hero-banners/starter-home-hero.png');
});

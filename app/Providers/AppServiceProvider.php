<?php

namespace App\Providers;

use App\Filament\Storybook\Livewire\BlockStoryPreview;
use App\Filament\Storybook\Livewire\BlockStoryRenderer;
use App\Filament\Storybook\Livewire\FormStoryRenderer;
use App\Filament\Storybook\Livewire\FormStoryPreview;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('story-block-renderer', BlockStoryRenderer::class);
        Livewire::component('story-block-preview', BlockStoryPreview::class);
        Livewire::component('story-form-renderer', FormStoryRenderer::class);
        Livewire::component('story-form-preview', FormStoryPreview::class);
    }
}

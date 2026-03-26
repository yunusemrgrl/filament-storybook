<?php

namespace App\Filament\Storybook\Pages;

use App\Filament\Storybook\AbstractStory;
use App\Filament\Storybook\StoryRegistry;
use Filament\Pages\Page;

class StoryPage extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.storybook.pages.story-page';

    protected static bool $shouldRegisterNavigation = false;

    public string $activePreset = '';

    public function mount(): void
    {
        $story = $this->getCurrentStory();

        if (! $story) {
            return;
        }

        $requestedPreset = request()->query('preset');

        if (filled($requestedPreset) && $story->hasVariant($requestedPreset)) {
            $this->activePreset = $requestedPreset;

            return;
        }

        $this->activePreset = $story->getDefaultVariantKey() ?? '';
    }

    public function getTitle(): string
    {
        $story = $this->getCurrentStory();

        if (! $story) {
            return 'Story Not Found';
        }

        if ($this->isOverview()) {
            return $story->title;
        }

        $presetLabel = $story->getVariantLabel($this->activePreset);

        return "{$story->title} / {$presetLabel}";
    }

    public function isOverview(): bool
    {
        return blank(request()->query('preset'));
    }

    public function getCurrentStory(): ?AbstractStory
    {
        $slug = request()->query('slug', '');

        if ($slug === '') {
            return null;
        }

        return StoryRegistry::findBySlug($slug);
    }

    protected function getViewData(): array
    {
        $story = $this->getCurrentStory();

        return [
            'story' => $story,
            'storyGroups' => StoryRegistry::grouped(),
            'presets' => $story ? $story->variants() : [],
            'activePreset' => $this->activePreset,
            'renderType' => $story ? $story->getRenderType() : 'generic',
            'slug' => request()->query('slug', ''),
            'isOverview' => $this->isOverview(),
        ];
    }
}

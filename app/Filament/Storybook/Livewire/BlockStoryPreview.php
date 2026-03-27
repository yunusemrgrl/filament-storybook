<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockFactory;
use App\Filament\Storybook\Blocks\ResolvedBlock;
use App\Filament\Storybook\StoryRegistry;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BlockStoryPreview extends Component
{
    public string $slug = '';

    public string $preset = 'default';

    public ?string $errorMessage = null;

    public function mount(string $slug, string $preset): void
    {
        $this->slug = $slug;
        $this->preset = $preset;

        if (! $this->getStory()) {
            $this->errorMessage = "Story bulunamadi: {$slug}";
        }
    }

    public function render(): View
    {
        return view('filament.storybook.livewire.block-story-preview', [
            'resolvedBlock' => $this->getResolvedBlock(),
        ]);
    }

    private function getStory(): ?AbstractBlockStory
    {
        $story = StoryRegistry::findBySlug($this->slug);

        return $story instanceof AbstractBlockStory ? $story : null;
    }

    private function getResolvedBlock(): ?ResolvedBlock
    {
        $story = $this->getStory();

        if (! $story) {
            return null;
        }

        try {
            $this->errorMessage = null;

            return app(BlockFactory::class)->make($story->getPresetPayload($this->preset));
        } catch (\Throwable) {
            $this->errorMessage = 'Block preview olusturulamadi.';

            return null;
        }
    }
}

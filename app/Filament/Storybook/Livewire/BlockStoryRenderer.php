<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockFactory;
use App\Filament\Storybook\Blocks\ResolvedBlock;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\StoryRegistry;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BlockStoryRenderer extends Component
{
    public string $slug = '';

    public string $preset = 'default';

    /**
     * @var array<string, mixed>
     */
    public array $knobValues = [];

    public ?string $errorMessage = null;

    public function mount(string $slug, string $preset = 'default'): void
    {
        $this->slug = $slug;
        $this->preset = $preset;

        $story = $this->getStory();

        if (! $story) {
            $this->errorMessage = "Story bulunamadi: {$slug}";

            return;
        }

        $this->knobValues = $story->getPresetValues($preset);
    }

    public function loadPreset(string $preset): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->preset = $preset;
        $this->knobValues = $story->getPresetValues($preset);
    }

    public function toggleBooleanKnob(string $name): void
    {
        $this->knobValues[$name] = ! (bool) ($this->knobValues[$name] ?? false);
    }

    public function resetPreview(): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->knobValues = $story->getPresetValues($this->preset);
    }

    /**
     * @return KnobDefinition[]
     */
    public function getKnobDefinitions(): array
    {
        $story = $this->getStory();

        return $story?->getVisibleKnobs($this->preset) ?? [];
    }

    /**
     * @return array<string, array<int, KnobDefinition>>
     */
    public function getGroupedKnobDefinitions(): array
    {
        $groupedKnobs = [];

        foreach ($this->getKnobDefinitions() as $knob) {
            $groupedKnobs[$knob->getGroup()][] = $knob;
        }

        return $groupedKnobs;
    }

    public function getPreviewSchemaFingerprint(): string
    {
        $payload = json_encode([
            'preset' => $this->preset,
            'knobs' => $this->knobValues,
        ]);

        return md5($payload ?: $this->preset);
    }

    public function render(): View
    {
        return view('filament.storybook.livewire.block-story-renderer', [
            'groupedKnobDefinitions' => $this->getGroupedKnobDefinitions(),
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

            return app(BlockFactory::class)->make(
                $story->makeBlockPayload($this->knobValues, $this->preset),
            );
        } catch (\Throwable) {
            $this->errorMessage = 'Block payload cozulurken bir hata olustu.';

            return null;
        }
    }
}

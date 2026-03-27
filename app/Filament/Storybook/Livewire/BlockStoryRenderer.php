<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockFactory;
use App\Filament\Storybook\Blocks\ResolvedBlock;
use App\Filament\Storybook\StoryRegistry;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class BlockStoryRenderer extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

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
        $this->knobsForm->fill($this->knobValues);
    }

    public function loadPreset(string $preset): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->preset = $preset;
        $this->knobValues = $story->getPresetValues($preset);
        $this->knobsForm->fill($this->knobValues);
    }

    public function knobsForm(Schema $schema): Schema
    {
        $story = $this->getStory();

        if (! $story) {
            return $schema->components([]);
        }

        return $schema
            ->components(app(KnobSchemaCompiler::class)->compile(
                $story->getVisibleKnobs($this->preset),
                live: true,
                testIdPrefix: 'knob',
            ))
            ->statePath('knobValues');
    }

    protected function getForms(): array
    {
        return [
            'knobsForm',
        ];
    }

    public function getPreviewSchemaFingerprint(): string
    {
        $payload = json_encode([
            'preset' => $this->preset,
            'knobs' => $this->knobValues,
        ]);

        return md5($payload ?: $this->preset);
    }

    public function resetPreview(): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->knobValues = $story->getPresetValues($this->preset);
        $this->knobsForm->fill($this->knobValues);
    }

    public function render(): View
    {
        return view('filament.storybook.livewire.block-story-renderer', [
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

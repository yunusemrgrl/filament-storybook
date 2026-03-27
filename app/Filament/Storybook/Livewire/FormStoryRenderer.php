<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\StoryRegistry;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormStoryRenderer extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public string $slug = '';

    public string $preset = 'default';

    /**
     * @var array<string, mixed>
     */
    public array $previewData = [];

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
            $this->errorMessage = "Story bulunamadı: {$slug}";

            return;
        }

        $this->knobValues = $story->getPresetValues($preset);
        $this->knobsForm->fill($this->knobValues);
        $this->fillPreviewFromPreset($story, $preset);
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
        $this->fillPreviewFromPreset($story, $preset);
    }

    public function previewForm(Schema $schema): Schema
    {
        $story = $this->getStory();

        if (! $story) {
            return $schema->components([]);
        }

        $built = $story->build($this->knobValues);
        $form = is_array($built) ? $built : [$built];

        return $schema
            ->components($form)
            ->columns($story->columns())
            ->statePath('previewData');
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
            'previewForm',
            'knobsForm',
        ];
    }

    public function updatedPreviewData(mixed $value, string $key): void
    {
        $statePath = "previewData.{$key}";

        $this->resetValidation($statePath);
        $this->validateOnly($statePath);
    }

    public function updatedKnobValues(): void
    {
        $this->resetValidation();

        try {
            $this->previewForm->getState();
        } catch (ValidationException) {
        }
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

        $this->resetValidation();
        $this->fillPreviewFromPreset($story, $this->preset);
    }

    public function render(): View
    {
        return view('filament.storybook.livewire.form-story-renderer');
    }

    private function getStory(): ?AbstractFormStory
    {
        $story = StoryRegistry::findBySlug($this->slug);

        return $story instanceof AbstractFormStory ? $story : null;
    }

    private function fillPreviewFromPreset(AbstractFormStory $story, string $preset): void
    {
        $this->resetValidation();
        $this->previewData = $story->getPresetPreviewData($preset);
        $this->previewForm->fill($this->previewData);
    }
}

<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\StoryRegistry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FormStoryPreview extends Component implements HasForms
{
    use InteractsWithForms;

    public string $slug = '';

    public string $preset = 'default';

    public array $previewData = [];

    public ?string $errorMessage = null;

    public function mount(string $slug, string $preset): void
    {
        $this->slug = $slug;
        $this->preset = $preset;

        if (! $this->getStory()) {
            $this->errorMessage = "Story bulunamadi: {$slug}";

            return;
        }

        $this->previewForm->fill([]);
    }

    public function previewForm(Schema $schema): Schema
    {
        $story = $this->getStory();

        if (! $story) {
            return $schema->components([]);
        }

        $built = $story->build($story->getPresetValues($this->preset));
        $fields = is_array($built) ? $built : [$built];

        return $schema
            ->components($fields)
            ->columns($story->columns())
            ->statePath('previewData');
    }

    protected function getForms(): array
    {
        return [
            'previewForm',
        ];
    }

    public function render(): View
    {
        return view('filament.storybook.livewire.form-story-preview');
    }

    private function getStory(): ?AbstractFormStory
    {
        $story = StoryRegistry::findBySlug($this->slug);

        return $story instanceof AbstractFormStory ? $story : null;
    }
}

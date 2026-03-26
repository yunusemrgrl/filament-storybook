<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\StoryRegistry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * FormStoryRenderer
 *
 * İki form paralel olarak yönetir:
 *
 * SOL — Preview formu
 * story->build($knobValues) çıktısını gerçek Filament formu olarak render eder.
 * Kullanıcı bu form üzerinde etkileşebilir (yazabilir, seçebilir vs.)
 * Ama bu bir "showcase" — submit etmez, sadece component'i gösterir.
 *
 * SAĞ — Knobs formu
 * story->knobs() listesini Filament form field'larına dönüştürür:
 *   KnobDefinition text    → TextInput
 *   KnobDefinition boolean → Toggle
 *   KnobDefinition select  → Select
 *   KnobDefinition number  → TextInput (numeric)
 * Kullanıcı knob değerini değiştirince $knobValues güncellenir
 * → preview formu reactive olarak yeniden render edilir.
 *
 * PRESET SİSTEMİ
 * StoryPage'den aktif preset değişince loadPreset() çağrılır,
 * $knobValues preset değerleriyle güncellenir,
 * iki form da yeniden render edilir.
 */
class FormStoryRenderer extends Component implements HasForms
{
    use InteractsWithForms;

    // -------------------------------------------------------------------------
    // Props
    // -------------------------------------------------------------------------

    public string $slug = '';

    public string $preset = 'default';

    // -------------------------------------------------------------------------
    // Form state'leri
    // -------------------------------------------------------------------------

    /**
     * Preview formunun state'i.
     * build() metodunun ürettiği field'ların değerleri burada yaşar.
     */
    public array $previewData = [];

    /**
     * Knobs formunun state'i.
     * Key: KnobDefinition name → Value: kullanıcının girdiği değer
     *
     * Örn: ['label' => 'Name', 'disabled' => false, 'prefix' => 'https://']
     */
    public array $knobValues = [];

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    public ?string $errorMessage = null;

    public ?string $previewValidationState = null;

    public ?string $previewValidationMessage = null;

    // -------------------------------------------------------------------------
    // Livewire lifecycle
    // -------------------------------------------------------------------------

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
        $this->fillPreviewFromPreset($story, $preset);
    }

    /**
     * StoryPage'den preset değişince çağrılır.
     * Knob değerlerini preset'e göre günceller.
     */
    public function loadPreset(string $preset): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->preset = $preset;
        $this->knobValues = $story->getPresetValues($preset);
        $this->fillPreviewFromPreset($story, $preset);
    }

    // -------------------------------------------------------------------------
    // Filament form tanımları
    // -------------------------------------------------------------------------

    /**
     * Sol taraftaki preview formu.
     * story->build($knobValues) her render'da çağrılır.
     * $knobValues değişince Livewire reactive olarak formu yeniler.
     */
    public function previewForm(Schema $schema): Schema
    {
        $story = $this->getStory();

        if (! $story) {
            return $schema->components([]);
        }

        // build() metodunu çağır — güncel knob değerleriyle field(lar) üret
        $built = $story->build($this->knobValues);

        // Tek field veya array olabilir
        $form = is_array($built) ? $built : [$built];

        return $schema
            ->components($form)
            ->columns($story->columns())
            ->statePath('previewData');
    }

    /**
     * Livewire'ın InteractsWithForms trait'i normalde tek form bekler.
     * Birden fazla form kullanmak için getForms() override edilir.
     */
    protected function getForms(): array
    {
        return [
            'previewForm',
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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

    public function toggleBooleanKnob(string $name): void
    {
        $this->knobValues[$name] = ! (bool) ($this->knobValues[$name] ?? false);
    }

    public function validatePreview(): void
    {
        $this->previewValidationState = null;
        $this->previewValidationMessage = null;

        try {
            $this->previewForm->getState();

            $this->previewValidationState = 'success';
            $this->previewValidationMessage = 'Preview, secili variant ve aktif knobs ile validation kontrolunden gecti.';
        } catch (ValidationException) {
            $this->previewValidationState = 'danger';
            $this->previewValidationMessage = 'Validation hatasi var. Preview icindeki field mesajlarini kontrol edin.';
        }
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

    private function getStory(): ?AbstractFormStory
    {
        $story = StoryRegistry::findBySlug($this->slug);

        return $story instanceof AbstractFormStory ? $story : null;
    }

    private function fillPreviewFromPreset(AbstractFormStory $story, string $preset): void
    {
        $this->previewValidationState = null;
        $this->previewValidationMessage = null;
        $this->resetValidation();
        $this->previewData = $story->getPresetPreviewData($preset);
        $this->previewForm->fill($this->previewData);
    }

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------

    public function render(): View
    {
        return view('filament.storybook.livewire.form-story-renderer', [
            'groupedKnobDefinitions' => $this->getGroupedKnobDefinitions(),
        ]);
    }
}

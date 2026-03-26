<?php

namespace App\Filament\Storybook\Livewire;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\StoryRegistry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Livewire\Attributes\On;
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

        // Knob'ları preset değerleriyle başlat
        $this->knobValues = $story->getPresetValues($preset);

        // Preview formu boş başlar (kullanıcı dolduracak)
        $this->previewForm->fill([]);

        // Knobs formu mevcut knob değerleriyle başlar
        $this->knobsForm->fill($this->knobValues);
    }

    /**
     * StoryPage'den preset değişince çağrılır.
     * Knob değerlerini preset'e göre günceller.
     */
    #[On('load-preset')]
    public function loadPreset(string $preset): void
    {
        $story = $this->getStory();

        if (! $story) {
            return;
        }

        $this->preset = $preset;
        $this->knobValues = $story->getPresetValues($preset);

        // Knobs formunu yeni değerlerle yenile
        $this->knobsForm->fill($this->knobValues);

        // Preview formunu sıfırla
        $this->previewForm->fill([]);
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
     * Sağ taraftaki knobs formu.
     * story->knobs() listesini Filament field'larına dönüştürür.
     *
     * Bu form değişince updatedKnobValues() tetiklenir
     * → previewForm reactive olarak güncellenir.
     */
    public function knobsForm(Schema $schema): Schema
    {
        $story = $this->getStory();

        if (! $story) {
            return $schema->components([]);
        }

        $fields = [];

        foreach ($story->knobs() as $knob) {
            $fields[] = $this->knobToField($knob);
        }

        return $schema
            ->components($fields)
            ->columns(1)
            ->statePath('knobValues');
    }

    /**
     * KnobDefinition'ı Filament form field'ına dönüştürür.
     */
    private function knobToField(KnobDefinition $knob): mixed
    {
        return match ($knob->getType()) {

            KnobDefinition::TYPE_BOOLEAN => Toggle::make($knob->getName())
                ->label($knob->getLabel())
                ->helperText($knob->getHelperText())
                ->inline(false)   // toggle'ı label'ın altına koy, yanına değil
                ->live(),

            KnobDefinition::TYPE_SELECT => Select::make($knob->getName())
                ->label($knob->getLabel())
                ->options($knob->getOptions())
                ->helperText($knob->getHelperText())
                ->live(),

            KnobDefinition::TYPE_NUMBER => TextInput::make($knob->getName())
                ->label($knob->getLabel())
                ->numeric()
                ->helperText($knob->getHelperText())
                ->live(),

            // TYPE_TEXT ve default
            default => TextInput::make($knob->getName())
                ->label($knob->getLabel())
                ->helperText($knob->getHelperText())
                ->live(onBlur: true), // text için her tuşta değil, focus çıkınca güncelle
        };
    }

    /**
     * Livewire'ın InteractsWithForms trait'i normalde tek form bekler.
     * Birden fazla form kullanmak için getForms() override edilir.
     */
    protected function getForms(): array
    {
        return [
            'previewForm',
            'knobsForm',
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

        return $story?->knobs() ?? [];
    }

    public function toggleBooleanKnob(string $name): void
    {
        $this->knobValues[$name] = ! (bool) ($this->knobValues[$name] ?? false);
    }

    private function getStory(): ?AbstractFormStory
    {
        $story = StoryRegistry::findBySlug($this->slug);

        return $story instanceof AbstractFormStory ? $story : null;
    }

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('filament.storybook.livewire.form-story-renderer', [
            'knobDefinitions' => $this->getKnobDefinitions(),
        ]);
    }
}

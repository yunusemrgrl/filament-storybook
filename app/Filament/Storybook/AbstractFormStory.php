<?php

namespace App\Filament\Storybook;

use Filament\Forms\Components\Field;

/**
 * AbstractFormStory
 *
 * Knobs sistemini kullanan form story'lerinin temel sınıfı.
 *
 * Alt sınıf iki şey yazmak zorunda:
 *
 * 1. knobs() → hangi prop'lar kontrol edilebilir, tipleri ve default'ları ne?
 *    public function knobs(): array
 *    {
 *        return [
 *            KnobDefinition::make('label')->text()->default('Name'),
 *            KnobDefinition::make('disabled')->boolean()->default(false),
 *        ];
 *    }
 *
 * 2. build(array $knobs) → güncel knob değerleriyle Filament field oluştur
 *    public function build(array $knobs): Field
 *    {
 *        return TextInput::make('preview')
 *            ->label($knobs['label'])
 *            ->disabled($knobs['disabled']);
 *    }
 *
 * Opsiyonel:
 * 3. presets() → knob'ları önceden doldurulmuş named başlangıç setleri
 *    public function presets(): array
 *    {
 *        return [
 *            'default'      => [],
 *            'disabled'     => ['disabled' => true],
 *            'with_prefix'  => ['prefix'   => 'https://'],
 *        ];
 *    }
 */
abstract class AbstractFormStory extends AbstractStory
{
    /**
     * Render motoru bu değeri okuyarak
     * FormStoryRenderer'ı devreye sokar.
     */
    public function getRenderType(): string
    {
        return 'form';
    }

    /**
     * Bu component'in hangi prop'ları knob olarak kontrol edilebilir?
     * Her eleman bir KnobDefinition instance'ı olmalı.
     *
     * @return KnobDefinition[]
     */
    abstract public function knobs(): array;

    /**
     * Güncel knob değerleriyle Filament field oluştur ve döndür.
     * FormStoryRenderer her knob değişikliğinde bu metodu çağırır.
     *
     * $knobs array'i: ['label' => 'Name', 'disabled' => false, ...]
     * Key'ler knobs() metodundaki KnobDefinition name'leriyle eşleşir.
     *
     * Tek field yerine birden fazla field döndürmek istersen array kullan:
     * return [TextInput::make('first'), TextInput::make('last')];
     *
     * @param  array  $knobs  Güncel knob değerleri
     * @return Field|Field[] Filament form field(ları)
     */
    abstract public function build(array $knobs): mixed;

    /**
     * Preset'ler: belirli bir kullanım senaryosunu tek tıkla yükle.
     * Her preset, default knob değerlerinin üstüne yazılacak değerleri içerir.
     * Boş array = tüm knob'lar default değerlerinde.
     *
     * @return array<string, array>
     */
    public function presets(): array
    {
        return [
            'default' => [],
        ];
    }

    /**
     * Tüm knob'ların default değerlerini key-value array olarak döner.
     * FormStoryRenderer başlangıç state'ini bununla kurar.
     *
     * @return array<string, mixed>
     */
    public function getKnobDefaults(): array
    {
        $defaults = [];

        foreach ($this->knobs() as $knob) {
            $defaults[$knob->getName()] = $knob->getDefault();
        }

        return $defaults;
    }

    /**
     * Preset adına göre knob değerlerini döner.
     * Default'ların üstüne preset değerleri yazılır.
     *
     * @param  string  $preset  Preset adı
     * @return array<string, mixed>
     */
    public function getPresetValues(string $preset): array
    {
        $presets = $this->presets();

        if (! array_key_exists($preset, $presets)) {
            return $this->getKnobDefaults();
        }

        return array_merge(
            $this->getKnobDefaults(),
            $presets[$preset]
        );
    }

    /**
     * Gallery ve playground preview'unda gosterilecek ornek field state'leri.
     *
     * @return array<string, array<string, mixed>>
     */
    public function presetPreviewData(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPresetPreviewData(string $preset): array
    {
        return $this->presetPreviewData()[$preset] ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function presetVisibleKnobs(): array
    {
        return [];
    }

    /**
     * @return KnobDefinition[]
     */
    public function getVisibleKnobs(string $preset): array
    {
        $visibleKnobNames = $this->presetVisibleKnobs();

        if (! array_key_exists($preset, $visibleKnobNames)) {
            return $this->knobs();
        }

        $allowedKnobs = array_flip($visibleKnobNames[$preset]);

        return array_values(array_filter(
            $this->knobs(),
            fn (KnobDefinition $knob): bool => array_key_exists($knob->getName(), $allowedKnobs),
        ));
    }

    /**
     * AbstractStory'nin variants() metodunu burada karşılıyoruz.
     * Knobs sisteminde "variant" yerine "preset" kavramı kullanılıyor.
     * Ama AbstractStory hâlâ variants() istiyor — preset isimlerini döndürüyoruz.
     */
    public function variants(): array
    {
        return array_keys($this->presets());
    }

    /**
     * Form grid sütun sayısı.
     */
    public function columns(): int
    {
        return 1;
    }

    /**
     * @return array<string, array{
     *     title?: string,
     *     description?: string,
     *     code?: string|null,
     *     points?: array<int, string>
     * }>
     */
    public function presetDocs(): array
    {
        return [];
    }

    /**
     * @return array{
     *     title?: string,
     *     description?: string,
     *     code?: string|null,
     *     points?: array<int, string>
     * }
     */
    public function getPresetDoc(string $preset): array
    {
        return $this->presetDocs()[$preset] ?? [];
    }

    public function getPresetTitle(string $preset): string
    {
        return $this->getPresetDoc($preset)['title'] ?? $this->getVariantLabel($preset);
    }

    public function getPresetDescription(string $preset): string
    {
        return $this->getPresetDoc($preset)['description'] ?? '';
    }

    public function getPresetCode(string $preset): ?string
    {
        return $this->getPresetDoc($preset)['code'] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function getPresetPoints(string $preset): array
    {
        return $this->getPresetDoc($preset)['points'] ?? [];
    }
}

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
abstract class AbstractFormStory extends AbstractKnobStory
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
     * Form grid sütun sayısı.
     */
    public function columns(): int
    {
        return 1;
    }

}

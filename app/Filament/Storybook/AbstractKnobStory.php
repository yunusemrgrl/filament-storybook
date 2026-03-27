<?php

namespace App\Filament\Storybook;

/**
 * @extends AbstractStory
 */
abstract class AbstractKnobStory extends AbstractStory
{
    /**
     * Bu story'nin hangi prop'lari knob olarak kontrol edilebilir?
     *
     * @return KnobDefinition[]
     */
    abstract public function knobs(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function presets(): array
    {
        return [
            'default' => [],
        ];
    }

    /**
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
            $presets[$preset],
        );
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
        $knobs = array_values(array_filter(
            $this->knobs(),
            fn (KnobDefinition $knob): bool => $knob->supportsPreset($preset),
        ));

        $visibleKnobNames = $this->presetVisibleKnobs();

        if (! array_key_exists($preset, $visibleKnobNames)) {
            return $knobs;
        }

        $allowedKnobs = array_flip($visibleKnobNames[$preset]);

        return array_values(array_filter(
            $knobs,
            fn (KnobDefinition $knob): bool => array_key_exists($knob->getName(), $allowedKnobs),
        ));
    }

    public function variants(): array
    {
        return array_keys($this->presets());
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

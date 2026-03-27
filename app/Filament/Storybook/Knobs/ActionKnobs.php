<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;

class ActionKnobs
{
    /**
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    public static function primaryCta(
        string $prefix = 'primaryCta',
        string $defaultText = 'Kesfet',
        string $defaultUrl = '/products',
        array $supports = [],
    ): array {
        $knobs = [
            KnobDefinition::make("{$prefix}Text")
                ->label('Primary CTA text')
                ->text()
                ->default($defaultText)
                ->group('Actions')
                ->page()
                ->helperText('Blok icindeki ana buton metni.'),
            KnobDefinition::make("{$prefix}Url")
                ->label('Primary CTA URL')
                ->text()
                ->default($defaultUrl)
                ->group('Actions')
                ->page()
                ->helperText('CTA tiklandiginda gidilecek relative veya absolute URL.'),
        ];

        return self::applySupports($knobs, $supports);
    }

    /**
     * @param  array<int, KnobDefinition>  $knobs
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    private static function applySupports(array $knobs, array $supports): array
    {
        if ($supports === []) {
            return $knobs;
        }

        foreach ($knobs as $knob) {
            $knob->supports($supports);
        }

        return $knobs;
    }
}

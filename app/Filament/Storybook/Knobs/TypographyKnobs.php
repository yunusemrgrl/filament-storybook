<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;

class TypographyKnobs
{
    /**
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    public static function alignment(array $supports = []): array
    {
        $knobs = [
            KnobDefinition::make('textAlign')
                ->label('Text alignment')
                ->select([
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                ])
                ->default('center')
                ->group('Typography')
                ->page()
                ->helperText('Headline ve supporting copy hangi eksende hizalansin?'),
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

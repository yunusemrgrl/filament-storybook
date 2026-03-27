<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;

class LayoutKnobs
{
    /**
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    public static function spacing(array $supports = []): array
    {
        $knobs = [
            KnobDefinition::make('paddingTop')
                ->label('Padding top')
                ->select(self::spacingOptions())
                ->default('lg')
                ->group('Layout / Spacing')
                ->page()
                ->helperText('Blok ustunde birakilacak dikey nefes alani.'),
            KnobDefinition::make('paddingBottom')
                ->label('Padding bottom')
                ->select(self::spacingOptions())
                ->default('lg')
                ->group('Layout / Spacing')
                ->page()
                ->helperText('Blok altinda birakilacak dikey nefes alani.'),
        ];

        return self::applySupports($knobs, $supports);
    }

    /**
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    public static function columns(array $supports = []): array
    {
        $knobs = [
            KnobDefinition::make('columns')
                ->label('Columns')
                ->select([
                    '2' => '2 columns',
                    '3' => '3 columns',
                    '4' => '4 columns',
                ])
                ->default('4')
                ->group('Layout / Grid')
                ->page()
                ->helperText('Desktop kiriliminda kac kolonluk bir grid istedigini belirler.'),
            KnobDefinition::make('cardGap')
                ->label('Card gap')
                ->select([
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large',
                ])
                ->default('md')
                ->group('Layout / Grid')
                ->page()
                ->helperText('Kartlar arasindaki bosluk tokeni.'),
        ];

        return self::applySupports($knobs, $supports);
    }

    /**
     * @return array<string, string>
     */
    private static function spacingOptions(): array
    {
        return [
            'none' => 'None',
            'sm' => 'Small',
            'md' => 'Medium',
            'lg' => 'Large',
            'xl' => 'Extra Large',
        ];
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

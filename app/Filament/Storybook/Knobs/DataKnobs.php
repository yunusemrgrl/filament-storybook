<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;

class DataKnobs
{
    /**
     * @param  array<int, string>  $supports
     * @return array<int, KnobDefinition>
     */
    public static function listing(
        int $defaultItemCount = 4,
        string $defaultCollection = 'Spring collection',
        array $supports = [],
    ): array {
        $knobs = [
            KnobDefinition::make('collectionLabel')
                ->label('Collection label')
                ->text()
                ->default($defaultCollection)
                ->group('Data')
                ->page()
                ->helperText('Editor tarafinda secilen koleksiyon veya query etiketinin insan okunur hali.'),
            KnobDefinition::make('itemCount')
                ->label('Item count')
                ->number()
                ->default($defaultItemCount)
                ->group('Data')
                ->page()
                ->helperText('Preview icin kac urun karti gosterilecegi.'),
            KnobDefinition::make('showPrices')
                ->label('Show prices')
                ->boolean()
                ->default(true)
                ->group('Data')
                ->page()
                ->helperText('Kartlar icinde fiyat satiri render edilsin mi?'),
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

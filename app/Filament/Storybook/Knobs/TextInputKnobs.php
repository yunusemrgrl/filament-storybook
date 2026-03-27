<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;
use Filament\Support\Icons\Heroicon;

class TextInputKnobs
{
    /**
     * @return array<int, KnobDefinition>
     */
    public static function affixes(): array
    {
        return [
            KnobDefinition::make('prefix')->label('prefix')->text()->default('')->group('Content')->helperText('Input soluna eklenen sabit metin.'),
            KnobDefinition::make('suffix')->label('suffix')->text()->default('')->group('Content')->helperText('Input sagina eklenen sabit metin.'),
        ];
    }

    public static function revealable(array $supports = ['password']): KnobDefinition
    {
        return KnobDefinition::make('revealable')
            ->label('revealable')
            ->boolean()
            ->default(false)
            ->group('State')
            ->component()
            ->supports($supports)
            ->helperText('Sadece password fieldlarda sagdaki aksiyonla degeri gecici olarak gosterir.');
    }

    /**
     * @param  array<int, string>  $prefixSupports
     * @param  array<int, string>  $suffixSupports
     * @return array<int, KnobDefinition>
     */
    public static function adornments(array $prefixSupports = [], array $suffixSupports = []): array
    {
        return [
            self::prefixIcon($prefixSupports),
            self::suffixIcon($suffixSupports),
            self::prefixIconColor($prefixSupports),
            self::suffixIconColor($suffixSupports),
        ];
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public static function numericValidation(): array
    {
        return [
            KnobDefinition::make('minValue')->label('minValue')->number()->default(null)->group('Validation')->helperText('Numeric inputlar icin minimum deger.'),
            KnobDefinition::make('maxValue')->label('maxValue')->number()->default(null)->group('Validation')->helperText('Numeric inputlar icin maksimum deger.'),
            KnobDefinition::make('step')->label('step')->select([
                '' => 'Default',
                '1' => '1',
                '0.01' => '0.01',
                'any' => 'Any',
            ])->default('')->group('Validation')->helperText('HTML step attr ile klavye/spinner davranisini etkiler.'),
        ];
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public static function utilities(): array
    {
        return [
            KnobDefinition::make('copyable')->label('copyable')->boolean()->default(false)->group('Utilities')->helperText('Suffix tarafina kopyalama aksiyonu ekler. HTTPS gerektirir.'),
            KnobDefinition::make('copyMessage')->label('copyMessage')->text()->default('Copied!')->group('Utilities')->helperText('Kopyalama sonrasi gosterilen toast metni.'),
            KnobDefinition::make('copyMessageDuration')->label('copyDuration')->number()->default(1500)->group('Utilities')->helperText('Kopyalama toast suresi, milisaniye cinsinden.'),
            KnobDefinition::make('maskPattern')->label('mask')->text()->default('')->group('Utilities')->helperText('Static Alpine mask. Ornek: 99/99/9999'),
        ];
    }

    /**
     * @param  array<int, string>  $supports
     */
    private static function prefixIcon(array $supports = []): KnobDefinition
    {
        return KnobDefinition::make('prefixIcon')
            ->label('prefixIcon')
            ->select(self::getAffixIconOptions())
            ->default('')
            ->group('Adornment')
            ->prototype()
            ->supports($supports)
            ->helperText('Curated Heroicon listesi. Primitive seviyede affix icon denemek icin kullanilir.');
    }

    /**
     * @param  array<int, string>  $supports
     */
    private static function suffixIcon(array $supports = []): KnobDefinition
    {
        return KnobDefinition::make('suffixIcon')
            ->label('suffixIcon')
            ->select(self::getAffixIconOptions())
            ->default('')
            ->group('Adornment')
            ->prototype()
            ->supports($supports)
            ->helperText('Sag affix iconu. Copyable veya revealable aksiyonlariyla cakismamasi icin secili presetlere gore acilir.');
    }

    /**
     * @param  array<int, string>  $supports
     */
    private static function prefixIconColor(array $supports = []): KnobDefinition
    {
        return KnobDefinition::make('prefixIconColor')
            ->label('prefixIconColor')
            ->select(self::getAffixIconColorOptions())
            ->default('')
            ->group('Adornment')
            ->prototype()
            ->supports($supports)
            ->helperText('Icon icin design-token benzeri renk secimi.');
    }

    /**
     * @param  array<int, string>  $supports
     */
    private static function suffixIconColor(array $supports = []): KnobDefinition
    {
        return KnobDefinition::make('suffixIconColor')
            ->label('suffixIconColor')
            ->select(self::getAffixIconColorOptions())
            ->default('')
            ->group('Adornment')
            ->prototype()
            ->supports($supports)
            ->helperText('Sag affix icon rengi.');
    }

    /**
     * @return array<string, string>
     */
    private static function getAffixIconOptions(): array
    {
        return [
            '' => 'None',
            Heroicon::GlobeAlt->value => 'Globe',
            Heroicon::Envelope->value => 'Envelope',
            Heroicon::Phone->value => 'Phone',
            Heroicon::User->value => 'User',
            Heroicon::LockClosed->value => 'Lock',
            Heroicon::CalendarDays->value => 'Calendar',
            Heroicon::BuildingOffice2->value => 'Office',
            Heroicon::CheckCircle->value => 'Check',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getAffixIconColorOptions(): array
    {
        return [
            '' => 'Default',
            'gray' => 'Gray',
            'primary' => 'Primary',
            'success' => 'Success',
            'warning' => 'Warning',
            'danger' => 'Danger',
            'info' => 'Info',
        ];
    }
}

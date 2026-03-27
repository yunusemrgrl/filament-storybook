<?php

namespace App\Filament\Storybook\Knobs;

use App\Filament\Storybook\KnobDefinition;

class CommonTextKnobs
{
    /**
     * @return array<int, KnobDefinition>
     */
    public static function content(string $defaultLabel = 'Name'): array
    {
        return [
            KnobDefinition::make('label')
                ->label('label')
                ->text()
                ->default($defaultLabel)
                ->group('Content')
                ->helperText('Field ustunde gorunen baslik.'),
            KnobDefinition::make('placeholder')
                ->label('placeholder')
                ->text()
                ->default('')
                ->group('Content')
                ->helperText('Alan bosken gorunen ipucu metni.'),
            KnobDefinition::make('helperText')
                ->label('helperText')
                ->text()->default('')
                ->group('Content')
                ->helperText('Field altinda gorunen sabit aciklama.'),
        ];
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public static function state(): array
    {
        return [
            KnobDefinition::make('required')
                ->label('required')
                ->boolean()
                ->default(false)
                ->group('State')
                ->helperText('Alan icin required validation kuralini ekler.'),
            KnobDefinition::make('disabled')
                ->label('disabled')
                ->boolean()
                ->default(false)
                ->group('State')
                ->helperText('Field etkilesimini ve submitte dehydrate olmasini kapatir.'),
            KnobDefinition::make('readOnly')
                ->label('readOnly')
                ->boolean()
                ->default(false)
                ->group('State')
                ->helperText('Field focus alir, ancak kullanici degeri duzenleyemez.'),
            KnobDefinition::make('trim')
                ->label('trim')
                ->boolean()
                ->default(false)
                ->group('State')
                ->helperText('Validation ve dehydration oncesi kenar bosluklarini temizler.'),
        ];
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public static function browser(): array
    {
        return [
            KnobDefinition::make('autocomplete')
                ->label('autocomplete')
                ->select([
                '' => 'Default',
                'off' => 'Off',
                'name' => 'Name',
                'email' => 'Email',
                'username' => 'Username',
                'url' => 'URL',
                'tel' => 'Telephone',
                'organization' => 'Organization',
                'new-password' => 'New Password',
                'one-time-code' => 'One Time Code',
                ])
                ->default('')
                ->group('Browser')
                ->helperText('Tarayicinin autocomplete davranisini belirler.'),
            KnobDefinition::make('autocapitalize')
                ->label('autocapitalize')
                ->select([
                '' => 'Default',
                'off' => 'Off',
                'sentences' => 'Sentences',
                'words' => 'Words',
                'characters' => 'Characters',
                ])
                ->default('')
                ->group('Browser')
                ->helperText('Mobil klavyelerde otomatik buyuk harf davranisini degistirir.'),
            KnobDefinition::make('inputMode')
                ->label('inputMode')
                ->select([
                '' => 'Default',
                'text' => 'Text',
                'email' => 'Email',
                'url' => 'URL',
                'tel' => 'Telephone',
                'numeric' => 'Numeric',
                'decimal' => 'Decimal',
                'search' => 'Search',
                ])
                ->default('')
                ->group('Browser')
                ->helperText('Mobil klavyeye hangi tipte giris beklendigini anlatir.'),
            KnobDefinition::make('datalistOptions')
                ->label('datalist')
                ->text()
                ->default('')
                ->group('Browser')
                ->helperText('Virgulle ayrilmis oneriler. Kullanici yine farkli deger girebilir.'),
        ];
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public static function stringValidation(): array
    {
        return [
            KnobDefinition::make('minLength')
                ->label('minLength')
                ->number()
                ->default(null)
                ->group('Validation')
                ->helperText('String uzunlugu icin alt sinir.'),
            KnobDefinition::make('maxLength')
                ->label('maxLength')
                ->number()
                ->default(null)
                ->group('Validation')
                ->helperText('String uzunlugu icin ust sinir.'),
            KnobDefinition::make('exactLength')
                ->label('length')
                ->number()
                ->default(null)
                ->group('Validation')
                ->helperText('Tam uzunluk eslesmesi gerekiyorsa kullanilir.'),
        ];
    }
}

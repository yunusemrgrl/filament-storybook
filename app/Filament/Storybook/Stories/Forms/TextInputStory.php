<?php

namespace App\Filament\Storybook\Stories\Forms;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use Filament\Forms\Components\TextInput;

class TextInputStory extends AbstractFormStory
{
    public string $title = 'TextInput';

    public string $group = 'Forms';

    public string $icon = 'heroicon-o-pencil';

    public string $description = 'TextInput, kisa string degerleri toplamak icin kullanilir. E-posta, sifre, URL ve sayisal girisler ayni component uzerinden sekillenir.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('label')
                ->label('label')
                ->text()
                ->default('Name')
                ->helperText('Field ustunde gorunen baslik.'),

            KnobDefinition::make('placeholder')
                ->label('placeholder')
                ->text()
                ->default('')
                ->helperText('Alan bosken gorunen ipucu metni.'),

            KnobDefinition::make('prefix')
                ->label('prefix')
                ->text()
                ->default('')
                ->helperText('Input soluna eklenen metin. Ornek: https://'),

            KnobDefinition::make('helperText')
                ->label('helperText')
                ->text()
                ->default('')
                ->helperText('Field altinda gorunen aciklama.'),

            KnobDefinition::make('required')
                ->label('required')
                ->boolean()
                ->default(false)
                ->helperText('Alani zorunlu yapar ve label yaninda isaret gosterir.'),

            KnobDefinition::make('disabled')
                ->label('disabled')
                ->boolean()
                ->default(false)
                ->helperText('Alani pasif yapar ve form submitine dahil etmez.'),

            KnobDefinition::make('readonly')
                ->label('readonly')
                ->boolean()
                ->default(false)
                ->helperText('Alan focus alir ama kullanici degeri dogrudan degistiremez.'),

            KnobDefinition::make('suffix')
                ->label('suffix')
                ->text()
                ->default('')
                ->helperText('Input sagina eklenen metin. Ornek: .com, USD'),
        ];
    }

    public function build(array $knobs): TextInput
    {
        $field = TextInput::make('preview')
            ->label($knobs['label'] ?? 'Name');

        if (! empty($knobs['placeholder'] ?? null)) {
            $field->placeholder($knobs['placeholder']);
        }

        if (! empty($knobs['helperText'] ?? null)) {
            $field->helperText($knobs['helperText']);
        }

        if (! empty($knobs['prefix'] ?? null)) {
            $field->prefix($knobs['prefix']);
        }

        if (! empty($knobs['suffix'] ?? null)) {
            $field->suffix($knobs['suffix']);
        }

        if (($knobs['disabled'] ?? false) === true) {
            $field->disabled();
        }

        if (($knobs['readonly'] ?? false) === true) {
            $field->readOnly();
        }

        if (($knobs['required'] ?? false) === true) {
            $field->required();
        }

        if (($knobs['email'] ?? false) === true) {
            $field->email();
        }

        if (($knobs['numeric'] ?? false) === true) {
            $field->numeric();
        }

        if (($knobs['password'] ?? false) === true) {
            $field->password();
        }

        if (($knobs['password'] ?? false) === true && ($knobs['revealable'] ?? false) === true) {
            $field->revealable();
        }

        return $field;
    }

    public function presets(): array
    {
        return [
            'text' => [
                'label' => 'Name',
            ],

            'email' => [
                'label' => 'Email',
                'placeholder' => 'name@example.com',
                'helperText' => 'Gecerli bir e-posta adresi girin.',
                'required' => true,
                'email' => true,
            ],

            'with_prefix' => [
                'label' => 'Website',
                'placeholder' => 'example.com',
                'prefix' => 'https://',
                'helperText' => 'Sitenizin tam adresini girin.',
                'required' => true,
            ],

            'password' => [
                'label' => 'Password',
                'placeholder' => '********',
                'password' => true,
                'revealable' => true,
                'required' => true,
            ],

            'numeric' => [
                'label' => 'Amount',
                'placeholder' => '100',
                'suffix' => 'USD',
                'numeric' => true,
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
use Filament\Forms\Components\TextInput;

TextInput::make('name')
PHP;
    }

    public function anatomy(): array
    {
        return [
            [
                'title' => 'Label',
                'description' => 'Fieldin ustunde yer alir ve kullanicinin ne girecegini tarif eder.',
            ],
            [
                'title' => 'Input wrapper',
                'description' => 'Placeholder, type, prefix ve suffix gibi tum giris davranisi burada toplanir.',
            ],
            [
                'title' => 'Helper text',
                'description' => 'Alan altinda ek baglam verir. Validation mesajindan farkli olarak sabit aciklamadir.',
            ],
            [
                'title' => 'Affixes and actions',
                'description' => 'Prefix, suffix, icon ve password reveal gibi yardimci kontroller inputun etrafinda konumlanir.',
            ],
        ];
    }

    public function documentationSections(): array
    {
        return [
            [
                'title' => 'HTML input types',
                'description' => 'TextInput, ayni API uzerinden email, numeric, integer, password, tel ve url gibi farkli HTML tiplerine gecebilir.',
                'code' => <<<'PHP'
TextInput::make('email')
    ->email()

TextInput::make('amount')
    ->numeric()

TextInput::make('password')
    ->password()
PHP,
                'points' => [
                    'email() yalnizca gorunumu degistirmez; Filament validation tarafini da buna gore kurar.',
                    'numeric() ve integer() sayisal giris deneyimini netlestirir.',
                    'password() ile revealable() birlikte kullanildiginda sifre gostergesi eklenebilir.',
                ],
            ],
            [
                'title' => 'Affixes',
                'description' => 'URL, domain, para birimi gibi senaryolarda inputun iki yanina sabit metin veya ikon eklenebilir.',
                'code' => <<<'PHP'
TextInput::make('domain')
    ->prefix('https://')
    ->suffix('.com')
PHP,
                'points' => [
                    'Prefix ve suffix, kullanicinin girmemesi gereken sabit parcayi ayirir.',
                    'Ozellikle URL, para ve olcu birimi girislerinde daha temiz bir zihinsel model sunar.',
                ],
            ],
            [
                'title' => 'Readonly vs disabled',
                'description' => 'Bu iki durum benzer gorunse de form davranisi acisindan farklidir.',
                'code' => <<<'PHP'
TextInput::make('name')
    ->readOnly()

TextInput::make('name')
    ->disabled()
PHP,
                'points' => [
                    'readOnly() alanin submitte gitmesini engellemez; sadece duzenlemeyi kisitlar.',
                    'disabled() alanin etkilesimini ve submitte gonderilmesini kapatir.',
                ],
            ],
            [
                'title' => 'Password and autocomplete',
                'description' => 'Sifre alanlari genellikle revealable ve autocomplete ayarlariyla birlikte dusunulur.',
                'code' => <<<'PHP'
TextInput::make('password')
    ->password()
    ->revealable()
    ->autocomplete('new-password')
PHP,
                'points' => [
                    'revealable() kullaniciya yazdigi degeri kontrol etme sansi verir.',
                    'autocomplete() tarayici sifre yoneticileriyle daha net iletisim kurar.',
                ],
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'text' => [
                'title' => 'Basic text',
                'description' => 'En sade TextInput kurulumu. Kisa string degerleri, isim ve baslik alanlari icin kullanilir.',
                'code' => <<<'PHP'
TextInput::make('name')
    ->label('Name')
PHP,
                'points' => [
                    'Minimal konfig, hizli form iskeleti icin dogru baslangictir.',
                    'Placeholder veya helper text gerekmediginde en temiz gorunum budur.',
                ],
            ],
            'email' => [
                'title' => 'Email input',
                'description' => 'E-posta girisi icin type ve validation semantigini birlikte kurar.',
                'code' => <<<'PHP'
TextInput::make('email')
    ->email()
    ->required()
    ->placeholder('name@example.com')
    ->helperText('Gecerli bir e-posta adresi girin.')
PHP,
                'points' => [
                    'email() HTML type ve validation davranisini ayni anda getirir.',
                    'required() ile birlikte kullanildiginda onboarding ve auth ekranlari icin guclu bir temel sunar.',
                ],
            ],
            'with_prefix' => [
                'title' => 'URL with prefix',
                'description' => 'Kullanicinin yalnizca degisken parcayi girmesini saglar; sabit protocol input disinda kalir.',
                'code' => <<<'PHP'
TextInput::make('website')
    ->prefix('https://')
    ->placeholder('example.com')
    ->required()
    ->helperText('Sitenizin tam adresini girin.')
PHP,
                'points' => [
                    'Prefix, kullanicinin protocolu tekrar tekrar yazmasini engeller.',
                    'URL benzeri alanlarda hata oranini azaltir.',
                ],
            ],
            'password' => [
                'title' => 'Password field',
                'description' => 'Sifre alanlari maskeleme, reveal ve zorunluluk davranisini birlikte ister.',
                'code' => <<<'PHP'
TextInput::make('password')
    ->password()
    ->revealable()
    ->required()
    ->placeholder('********')
PHP,
                'points' => [
                    'password() alanin gorunumunu ve tarayici davranisini duzenler.',
                    'revealable() kullanici deneyimini artirirken hala ayni field API uzerinde kalir.',
                ],
            ],
            'numeric' => [
                'title' => 'Numeric amount',
                'description' => 'Sayisal degerlerde suffix ile baglam ekleyip numeric() ile girisi netlestirebilirsiniz.',
                'code' => <<<'PHP'
TextInput::make('amount')
    ->numeric()
    ->suffix('USD')
    ->placeholder('100')
PHP,
                'points' => [
                    'numeric() klavye ve validation beklentisini sayisal alana ceker.',
                    'Suffix, kullanicinin girdigi degerin neyi temsil ettigini acikca gosterir.',
                ],
            ],
        ];
    }

    public function getExternalDocsUrl(): ?string
    {
        return 'https://filamentphp.com/docs/5.x/forms/text-input';
    }
}

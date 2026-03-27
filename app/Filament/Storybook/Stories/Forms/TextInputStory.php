<?php

namespace App\Filament\Storybook\Stories\Forms;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\Knobs\CommonTextKnobs;
use App\Filament\Storybook\Knobs\TextInputKnobs;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;

class TextInputStory extends AbstractFormStory
{
    public string $title = 'TextInput';

    public string $group = 'Forms';

    public string $icon = 'heroicon-o-pencil';

    public string $description = 'TextInput, tek satir string, password, URL, phone ve numeric girislerini ayni API uzerinde toplar. Browser semantigi, validation, masking ve utility aksiyonlari birlikte dusunulmelidir.';

    public function knobs(): array
    {
        return [
            ...CommonTextKnobs::content(defaultLabel: 'Name'),
            ...TextInputKnobs::affixes(),
            ...CommonTextKnobs::state(),
            TextInputKnobs::revealable(),
            ...TextInputKnobs::adornments(
                prefixSupports: ['text', 'email', 'with_prefix', 'password', 'masked_date', 'phone', 'autocomplete_company'],
                suffixSupports: ['text', 'email', 'with_prefix', 'masked_date', 'masked_amount', 'phone', 'autocomplete_company'],
            ),
            ...CommonTextKnobs::browser(),
            ...CommonTextKnobs::stringValidation(),
            ...TextInputKnobs::numericValidation(),
            ...TextInputKnobs::utilities(),
        ];
    }

    public function build(array $knobs): TextInput
    {
        $field = TextInput::make('preview')
            ->label((string) ($knobs['label'] ?? 'Name'))
            ->live(onBlur: true);

        if (($knobs['email'] ?? false) === true) {
            $field->email();
        }

        if (($knobs['url'] ?? false) === true) {
            $field->url();
        }

        if (($knobs['tel'] ?? false) === true) {
            $field->tel();
        }

        if (($knobs['numeric'] ?? false) === true) {
            $field->numeric();
        }

        if (($knobs['integer'] ?? false) === true) {
            $field->integer();
        }

        if (($knobs['password'] ?? false) === true) {
            $field->password();
        }

        if (($knobs['password'] ?? false) === true && ($knobs['revealable'] ?? false) === true) {
            $field->revealable();
        }

        if ($placeholder = $this->normalizeString($knobs['placeholder'] ?? null)) {
            $field->placeholder($placeholder);
        }

        if ($helperText = $this->normalizeString($knobs['helperText'] ?? null)) {
            $field->helperText($helperText);
        }

        if ($prefix = $this->normalizeString($knobs['prefix'] ?? null)) {
            $field->prefix($prefix);
        }

        if ($suffix = $this->normalizeString($knobs['suffix'] ?? null)) {
            $field->suffix($suffix);
        }

        if ($prefixIcon = $this->normalizeHeroicon($knobs['prefixIcon'] ?? null)) {
            $field->prefixIcon($prefixIcon);
        }

        if ($suffixIcon = $this->normalizeHeroicon($knobs['suffixIcon'] ?? null)) {
            $field->suffixIcon($suffixIcon);
        }

        if ($prefixIconColor = $this->normalizeString($knobs['prefixIconColor'] ?? null)) {
            $field->prefixIconColor($prefixIconColor);
        }

        if ($suffixIconColor = $this->normalizeString($knobs['suffixIconColor'] ?? null)) {
            $field->suffixIconColor($suffixIconColor);
        }

        if (($knobs['required'] ?? false) === true) {
            $field->required();
        }

        if (($knobs['disabled'] ?? false) === true) {
            $field->disabled();
        }

        if (($knobs['readOnly'] ?? false) === true) {
            $field->readOnly();
        }

        if (($knobs['trim'] ?? false) === true) {
            $field->trim();
        }

        if (($knobs['copyable'] ?? false) === true) {
            $field->copyable(
                $this->supportsClipboardCopy(),
                $this->normalizeString($knobs['copyMessage'] ?? null),
                $this->normalizeInteger($knobs['copyMessageDuration'] ?? null),
            );
        }

        if (($knobs['moneyMask'] ?? false) === true) {
            $field->mask(RawJs::make('$money($input)'))->stripCharacters(',');
        } elseif ($maskPattern = $this->normalizeString($knobs['maskPattern'] ?? null)) {
            $field->mask($maskPattern);
        }

        if ($autocomplete = $this->normalizeString($knobs['autocomplete'] ?? null)) {
            if ($autocomplete === 'off') {
                $field->autocomplete(false);
            } else {
                $field->autocomplete($autocomplete);
            }
        }

        if ($autocapitalize = $this->normalizeString($knobs['autocapitalize'] ?? null)) {
            if ($autocapitalize === 'off') {
                $field->autocapitalize(false);
            } else {
                $field->autocapitalize($autocapitalize);
            }
        }

        if ($inputMode = $this->normalizeString($knobs['inputMode'] ?? null)) {
            $field->inputMode($inputMode);
        }

        if ($step = $this->normalizeString($knobs['step'] ?? null)) {
            $field->step($step);
        }

        if ($telRegex = $this->normalizeString($knobs['telRegex'] ?? null)) {
            $field->telRegex($telRegex);
        }

        $datalistOptions = $this->parseDatalistOptions($knobs['datalistOptions'] ?? null);

        if ($datalistOptions !== []) {
            $field->datalist($datalistOptions);
        }

        if ($exactLength = $this->normalizeInteger($knobs['exactLength'] ?? null)) {
            $field->length($exactLength);
        } else {
            if ($minLength = $this->normalizeInteger($knobs['minLength'] ?? null)) {
                $field->minLength($minLength);
            }

            if ($maxLength = $this->normalizeInteger($knobs['maxLength'] ?? null)) {
                $field->maxLength($maxLength);
            }
        }

        if (($knobs['numeric'] ?? false) === true || ($knobs['integer'] ?? false) === true) {
            if (($minValue = $this->normalizeNumeric($knobs['minValue'] ?? null)) !== null) {
                $field->minValue($minValue);
            }

            if (($maxValue = $this->normalizeNumeric($knobs['maxValue'] ?? null)) !== null) {
                $field->maxValue($maxValue);
            }
        }

        return $field;
    }

    public function presets(): array
    {
        return [
            'text' => [
                'label' => 'Name',
                'placeholder' => 'Ada Lovelace',
                'helperText' => 'Kisa ve tanimlayici bir metin girin.',
                'trim' => true,
                'maxLength' => 255,
                'autocapitalize' => 'words',
                'autocomplete' => 'name',
            ],
            'email' => [
                'label' => 'Email address',
                'placeholder' => 'name@example.com',
                'helperText' => 'Gecerli bir e-posta adresi girin.',
                'required' => true,
                'email' => true,
                'trim' => true,
                'autocomplete' => 'email',
                'prefixIcon' => Heroicon::Envelope->value,
            ],
            'with_prefix' => [
                'label' => 'Website',
                'placeholder' => 'example.com',
                'prefix' => 'https://',
                'helperText' => 'Protocol sabit kalsin, kullanici yalnizca domain girsin.',
                'required' => true,
                'url' => true,
                'autocomplete' => 'url',
                'suffixIcon' => Heroicon::GlobeAlt->value,
                'suffixIconColor' => 'primary',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Choose a strong password',
                'helperText' => 'Reveal aksiyonu ile yazilan degeri gecici olarak gosterir.',
                'required' => true,
                'password' => true,
                'revealable' => true,
                'autocomplete' => 'new-password',
                'prefixIcon' => Heroicon::LockClosed->value,
            ],
            'copyable_api_key' => [
                'label' => 'API key',
                'helperText' => 'Salt okunur bir secret degeri kopyalamak icin uygun varyant.',
                'readOnly' => true,
                'copyable' => true,
                'copyMessage' => 'Copied!',
                'copyMessageDuration' => 1500,
            ],
            'masked_date' => [
                'label' => 'Birthday',
                'placeholder' => 'MM/DD/YYYY',
                'helperText' => 'Static mask, kullanicinin yalnizca belirli bir formatta yazmasini yonlendirir.',
                'maskPattern' => '99/99/9999',
                'autocomplete' => 'off',
                'inputMode' => 'numeric',
                'prefixIcon' => Heroicon::CalendarDays->value,
            ],
            'masked_amount' => [
                'label' => 'Amount',
                'placeholder' => '0.00',
                'helperText' => 'Money mask gorsel formatlama yapar, stripCharacters ise server tarafinda statei temizler.',
                'suffix' => 'USD',
                'numeric' => true,
                'moneyMask' => true,
                'minValue' => 0,
                'step' => '0.01',
            ],
            'phone' => [
                'label' => 'Phone',
                'placeholder' => '+90 555 123 45 67',
                'helperText' => 'tel() HTML type ve varsayilan phone regex kontrolunu birlikte getirir.',
                'tel' => true,
                'autocomplete' => 'tel',
                'prefixIcon' => Heroicon::Phone->value,
            ],
            'autocomplete_company' => [
                'label' => 'Manufacturer',
                'placeholder' => 'Toyota',
                'helperText' => 'Datalist oneridir; kullaniciyi zorlamaz.',
                'autocomplete' => 'organization',
                'datalistOptions' => 'BMW, Ford, Mercedes-Benz, Porsche, Toyota, Volkswagen',
                'prefixIcon' => Heroicon::BuildingOffice2->value,
            ],
            'readonly' => [
                'label' => 'Order code',
                'helperText' => 'readOnly alanlar odaklanabilir ve submitte dehydrate olmaya devam eder.',
                'prefix' => '#',
                'readOnly' => true,
                'copyable' => true,
                'copyMessage' => 'Order code copied',
            ],
        ];
    }

    public function presetPreviewData(): array
    {
        return [
            'text' => ['preview' => 'Ada Lovelace'],
            'email' => ['preview' => 'ada@example.com'],
            'with_prefix' => ['preview' => 'filamentphp.com'],
            'password' => ['preview' => 'Sup3rSecret!'],
            'copyable_api_key' => ['preview' => 'sk_live_1234567890abcde'],
            'masked_date' => ['preview' => '12/25/2026'],
            'masked_amount' => ['preview' => '1250.75'],
            'phone' => ['preview' => '+90 555 123 45 67'],
            'autocomplete_company' => ['preview' => 'Mercedes-Benz'],
            'readonly' => ['preview' => 'ORD-2026-0042'],
        ];
    }

    public function presetVisibleKnobs(): array
    {
        $contentKnobs = ['label', 'placeholder', 'helperText'];
        $interactiveStateKnobs = ['required', 'disabled', 'readOnly'];
        $textStateKnobs = [...$interactiveStateKnobs, 'trim'];
        $copyableKnobs = ['copyable', 'copyMessage', 'copyMessageDuration'];

        return [
            'text' => [
                ...$contentKnobs,
                ...$textStateKnobs,
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'autocomplete',
                'autocapitalize',
                'minLength',
                'maxLength',
                'exactLength',
            ],
            'email' => [
                ...$contentKnobs,
                ...$textStateKnobs,
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'autocomplete',
                'minLength',
                'maxLength',
            ],
            'with_prefix' => [
                ...$contentKnobs,
                ...$interactiveStateKnobs,
                'prefix',
                'suffix',
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'autocomplete',
            ],
            'password' => [
                'label',
                'placeholder',
                'helperText',
                'required',
                'disabled',
                'revealable',
                'prefixIcon',
                'prefixIconColor',
                'autocomplete',
            ],
            'copyable_api_key' => [
                'label',
                'helperText',
                'readOnly',
                ...$copyableKnobs,
            ],
            'masked_date' => [
                ...$contentKnobs,
                ...$interactiveStateKnobs,
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'maskPattern',
                'inputMode',
            ],
            'masked_amount' => [
                ...$contentKnobs,
                ...$interactiveStateKnobs,
                'suffix',
                'suffixIcon',
                'suffixIconColor',
                'minValue',
                'maxValue',
                'step',
            ],
            'phone' => [
                ...$contentKnobs,
                ...$interactiveStateKnobs,
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'autocomplete',
            ],
            'autocomplete_company' => [
                ...$contentKnobs,
                'required',
                'disabled',
                'readOnly',
                'prefixIcon',
                'suffixIcon',
                'prefixIconColor',
                'suffixIconColor',
                'autocomplete',
                'autocapitalize',
                'datalistOptions',
            ],
            'readonly' => [
                'label',
                'helperText',
                'prefix',
                'suffix',
                'readOnly',
                ...$copyableKnobs,
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Name')
    ->required()
    ->maxLength(255)
    ->trim()
PHP;
    }

    public function anatomy(): array
    {
        return [
            [
                'title' => 'Label and validation state',
                'description' => 'Kullanici ne girecegini labeldan anlar; required isareti ve error metni ayni alan etrafinda toplanir.',
            ],
            [
                'title' => 'Input wrapper',
                'description' => 'Placeholder, prefix, suffix, icons ve copy/reveal aksiyonlari input wrapper uzerinde birlesir.',
            ],
            [
                'title' => 'Browser semantics',
                'description' => 'Type, autocomplete, autocapitalize, inputMode ve step tarayicinin nasil davranacagini belirler.',
            ],
            [
                'title' => 'Normalization pipeline',
                'description' => 'Mask, stripCharacters ve trim validation ile save arasindaki statei temizler.',
            ],
        ];
    }

    public function documentationSections(): array
    {
        return [
            [
                'title' => 'Decision model: variants, knobs, docs-only APIs',
                'description' => 'TextInput API genis oldugu icin her methodu knob yapmak dogru degil. Storybook tarafinda uc katman kullaniyoruz.',
                'code' => <<<'PHP'
TextInput::make('amount')
    ->numeric()
    ->minValue(0)
    ->maxValue(10000)
PHP,
                'points' => [
                    'Variantler gercek urun recipe leridir: email, password, copyable API key, masked amount gibi.',
                    'Knobs yalnizca guvenli scalar ayarlari acar: label, affix icon secimi, required, minLength, copyMessage, static mask gibi.',
                    'RawJs mask, regex veya custom rule builder gibi backend agirlikli kararlar docs olarak kalmali; serbest kullanici girdisine acilmamali.',
                ],
            ],
            [
                'title' => 'Semantic input types',
                'description' => 'TextInput sadece gorunus degistirmez; secilen type browser davranisi ve Filament rule katmanini da etkiler.',
                'code' => <<<'PHP'
TextInput::make('email')->email()

TextInput::make('password')
    ->password()
    ->revealable()

TextInput::make('phone')->tel()

TextInput::make('amount')->numeric()

TextInput::make('quantity')->integer()

TextInput::make('website')->url()
PHP,
                'points' => [
                    'email(), url(), tel(), numeric() ve integer() ilgili validation kurallarini otomatik ekler.',
                    'integer(), numeric() uzerine inputMode ve step varsayimlari da getirir.',
                    'revealable() password preset icinde knob olarak acilabilir; text veya email gibi variantlarda gizli kalmalidir.',
                ],
            ],
            [
                'title' => 'Browser UX knobs',
                'description' => 'Autocomplete, autocapitalize, datalist, inputMode ve step tarayici deneyimini guclendirir. Bunlar kullaniciya acilabilir cunku guvenli ve anlasilir ayarlardir.',
                'code' => <<<'PHP'
TextInput::make('manufacturer')
    ->autocomplete('organization')
    ->datalist([
        'BMW',
        'Ford',
        'Toyota',
    ])
    ->autocapitalize('words')
PHP,
                'points' => [
                    'datalist() strict secim degil, yalnizca oneridir. Zorunlu secim gerekiyorsa Select kullanilmalidir.',
                    'inputMode() ve step() ozellikle mobile numeric deneyimde faydalidir.',
                    'autocomplete(false) ve autocomplete("new-password") gibi kararlar security ve UX acisindan ayrica dusunulmelidir.',
                ],
            ],
            [
                'title' => 'Affixes, icons and copyable actions',
                'description' => 'URL, domain, para birimi ve secret key senaryolarinda input wrapper etrafindaki yardimci elemanlar daha net bir zihinsel model kurar.',
                'code' => <<<'PHP'
use Filament\Support\Icons\Heroicon;

TextInput::make('website')
    ->prefix('https://')
    ->suffixIcon(Heroicon::GlobeAlt)

TextInput::make('apiKey')
    ->readOnly()
    ->copyable(copyMessage: 'Copied!', copyMessageDuration: 1500)
PHP,
                'points' => [
                    'Prefix ve suffix, kullanicinin yazmamasi gereken sabit parcayi ayirir.',
                    'copyable() knob olarak acilabilir; ancak SSL olmadiginda browser clipboard API calismaz.',
                    'Icon secimi serbest text yerine curated asset select ile acilmalidir; bu storyde primitive seviyede knob olarak sunulur.',
                ],
            ],
            [
                'title' => 'Masking and normalization',
                'description' => 'Masking kullaniciyi yonlendirir; stripCharacters ve trim ise validation ile save oncesi statei normalize eder.',
                'code' => <<<'PHP'
use Filament\Support\RawJs;

TextInput::make('birthday')
    ->mask('99/99/9999')

TextInput::make('amount')
    ->mask(RawJs::make('$money($input)'))
    ->stripCharacters(',')
    ->numeric()
    ->trim()
PHP,
                'points' => [
                    'Static mask pattern playground knob olarak acilabilir; kullanici aninda etkisini gorur.',
                    'Dynamic RawJs mask cok guclu olsa da sandboxsiz kullanici girdisi icin fazla dusuk seviye oldugundan docs recipe olarak kalmalidir.',
                    'trim() global configureUsing ile de uygulanabilir; bu urun capinda bir standart ise service provider seviyesi daha dogrudur.',
                ],
            ],
            [
                'title' => 'Validation strategy',
                'description' => 'Basit validation sinirlari knobs ile denetlenebilir; veri modeli veya tenant bagimli kurallar ise backend recipe seviyesinde kalmalidir.',
                'code' => <<<'PHP'
TextInput::make('code')
    ->length(8)

TextInput::make('amount')
    ->numeric()
    ->minValue(1)
    ->maxValue(100)

TextInput::make('phone')
    ->tel()
    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
PHP,
                'points' => [
                    'required(), minLength(), maxLength(), length(), minValue() ve maxValue() playground icin ideal knobs tur.',
                    'telRegex(), regex(), custom rules(), scopedUnique() ve currentPassword() is kurali veya auth bagimli oldugundan preset/docs seviyesinde kalmali.',
                    'validationMessages(), validationAttribute() ve allowHtmlValidationMessages() backend hata dili kararlari oldugu icin serbest knob olmamali.',
                ],
            ],
            [
                'title' => 'Advanced backend-only APIs',
                'description' => 'Bazi methodlar ancak gercek form kaydetme akisi ve model baglami icinde anlamlidir.',
                'code' => <<<'PHP'
TextInput::make('current_password')
    ->password()
    ->currentPassword()

TextInput::make('email')
    ->scopedUnique()
    ->validationMessages([
        'unique' => 'The :attribute has already been registered.',
    ])

TextInput::make('name')
    ->required()
    ->saved(false)
    ->validatedWhenNotDehydrated(false)
PHP,
                'points' => [
                    'currentPassword() auth bagimli oldugu icin ancak gercek kimlik dogrulama formlarinda test edilmelidir.',
                    'scopedUnique() multi tenancy ve soft delete kapsami gibi Eloquent baglamina dayanir; demo knob olmamali.',
                    'saved(false) ve validatedWhenNotDehydrated(false) davranislari plain preview yerine gercek submit akisi icinde anlatilmalidir.',
                ],
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'text' => [
                'title' => 'Basic text',
                'description' => 'Genel isim, baslik ve kisa metin alanlari icin sade baslangic kurgusu.',
                'code' => <<<'PHP'
TextInput::make('name')
    ->label('Name')
    ->trim()
    ->maxLength(255)
PHP,
                'points' => [
                    'Temel text alanlarinda trim ve maxLength cogu zaman yeterlidir.',
                    'Autocapitalize ve autocomplete gibi browser ipuclari bu kurguda anlamlidir.',
                ],
            ],
            'email' => [
                'title' => 'Email input',
                'description' => 'Email semantigini, validationi ve browser autocomplete davranisini birlikte kurar.',
                'code' => <<<'PHP'
TextInput::make('email')
    ->email()
    ->required()
    ->autocomplete('email')
    ->trim()
PHP,
                'points' => [
                    'email() HTML type ile birlikte email rule da ekler.',
                    'Email alanlari neredeyse her zaman trim ile birlikte dusunulmelidir.',
                ],
            ],
            'with_prefix' => [
                'title' => 'URL with prefix',
                'description' => 'Protocol sabit kalsin, kullanici sadece degisken parcayi girsin.',
                'code' => <<<'PHP'
use Filament\Support\Icons\Heroicon;

TextInput::make('website')
    ->url()
    ->prefix('https://')
    ->suffixIcon(Heroicon::GlobeAlt)
    ->required()
PHP,
                'points' => [
                    'Affixler yazilmamasi gereken sabit parcayi field disina tasir.',
                    'Suffix icon varsayilan recipe kararidir, ancak playground tarafinda curated knob ile degistirilebilir.',
                    'URL benzeri alanlarda hata oranini dusurur.',
                ],
            ],
            'password' => [
                'title' => 'Password with reveal',
                'description' => 'Password inputlari maskeleme, reveal ve uygun autocomplete ile birlikte kurgulanir.',
                'code' => <<<'PHP'
TextInput::make('password')
    ->password()
    ->revealable()
    ->autocomplete('new-password')
    ->required()
PHP,
                'points' => [
                    'revealable() ancak password() ile gecerlidir.',
                    'Bu storyde revealable, yalnizca password variantinda gorunen baglamsal bir knob olarak sunulur.',
                    'Autocomplete secimi auth senaryosuna gore belirlenmelidir.',
                ],
            ],
            'copyable_api_key' => [
                'title' => 'Copyable API key',
                'description' => 'Read-only secret degerler icin suffix kopyalama aksiyonu ekler.',
                'code' => <<<'PHP'
TextInput::make('apiKey')
    ->readOnly()
    ->copyable(copyMessage: 'Copied!', copyMessageDuration: 1500)
PHP,
                'points' => [
                    'copyable() browser clipboard API kullandigi icin HTTPS gerektirir.',
                    'readOnly burada disabled yerine dogru secimdir; deger gorunur ve fokuslanabilir kalir.',
                ],
            ],
            'masked_date' => [
                'title' => 'Masked date',
                'description' => 'Static Alpine mask ile belirli formatta veri girisini yonlendirir.',
                'code' => <<<'PHP'
TextInput::make('birthday')
    ->mask('99/99/9999')
    ->placeholder('MM/DD/YYYY')
PHP,
                'points' => [
                    'Static mask pattern, knob ile degistirildiginde etkisi hemen gorulebilir.',
                    'Mask, format yonetir; domain validation ihtiyaci varsa ek rule gerektirir.',
                ],
            ],
            'masked_amount' => [
                'title' => 'Masked amount',
                'description' => 'Money mask gorsel formatlama yaparken numeric validation ve stripCharacters ile state temizlenir.',
                'code' => <<<'PHP'
use Filament\Support\RawJs;

TextInput::make('amount')
    ->mask(RawJs::make('$money($input)'))
    ->stripCharacters(',')
    ->numeric()
    ->suffix('USD')
PHP,
                'points' => [
                    'Dynamic mask gercek urun recipe seviyesinde tutulmali; serbest user JS almayin.',
                    'Maskli degerin validationdan once normalize edilmesi gerekir.',
                ],
            ],
            'phone' => [
                'title' => 'Phone validation',
                'description' => 'Telefon alaninda tel() ve gerekirse telRegex() birlikte dusunulur.',
                'code' => <<<'PHP'
TextInput::make('phone')
    ->tel()
    ->autocomplete('tel')
PHP,
                'points' => [
                    'tel() varsayilan phone regexi de ekler.',
                    'Ulkeye ozel regex gerekiyorsa bunu docs recipe olarak tutmak daha dogrudur.',
                ],
            ],
            'autocomplete_company' => [
                'title' => 'Autocomplete suggestions',
                'description' => 'Browser autocomplete ile datalist onerilerini birlestiren bir arama benzeri deneyim.',
                'code' => <<<'PHP'
TextInput::make('manufacturer')
    ->autocomplete('organization')
    ->datalist([
        'BMW',
        'Ford',
        'Toyota',
    ])
PHP,
                'points' => [
                    'Datalist onerir ama kullaniciyi sabit seceneklere kilitlemez.',
                    'Strict secenek gerekiyorsa Select daha dogru componenttir.',
                ],
            ],
            'readonly' => [
                'title' => 'Read-only value',
                'description' => 'Deger gorunsun, fokus alabilsin ve gerekirse kopyalanabilsin; fakat duzenlenmesin.',
                'code' => <<<'PHP'
TextInput::make('orderCode')
    ->readOnly()
    ->copyable()
PHP,
                'points' => [
                    'readOnly alanlar submitte servera gitmeye devam eder.',
                    'Gecersiz degisikliklere guvenmemek icin gerekirse saved(false) ile birlikte dusunulmelidir.',
                ],
            ],
        ];
    }

    public function getExternalDocsUrl(): ?string
    {
        return 'https://filamentphp.com/docs/5.x/forms/text-input';
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeNumeric(mixed $value): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $numericValue = (float) $value;

        if ((int) $numericValue === $numericValue) {
            return (int) $numericValue;
        }

        return $numericValue;
    }

    private function normalizeHeroicon(mixed $value): ?Heroicon
    {
        if ($value instanceof Heroicon) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Heroicon::tryFrom($value);
    }

    /**
     * @return array<int, string>
     */
    private function parseDatalistOptions(mixed $value): array
    {
        if (! is_string($value)) {
            return [];
        }

        $options = array_map(
            static fn (string $option): string => trim($option),
            explode(',', $value),
        );

        return array_values(array_filter($options, static fn (string $option): bool => $option !== ''));
    }

    private function supportsClipboardCopy(): bool
    {
        $request = request();
        $host = strtolower((string) $request->getHost());

        return $request->isSecure() || in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}

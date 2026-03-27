<?php

namespace App\Filament\Storybook\Stories\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Filament\Storybook\Blocks\Data\HeroBannerBlockData;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\Knobs\ActionKnobs;
use App\Filament\Storybook\Knobs\LayoutKnobs;
use App\Filament\Storybook\Knobs\TypographyKnobs;

class HeroBannerBlockStory extends AbstractBlockStory
{
    public string $title = 'Hero Banner';

    public string $group = 'Page Blocks';

    public string $icon = 'heroicon-o-photo';

    public string $description = 'Landing page ve vitrin ekranlarinin ust bandinda kullanilan, headline, supporting copy ve CTA tasiyan ana karsilama blogu.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('headline')
                ->label('Headline')
                ->text()
                ->default('Struktura ile sinirlari kaldirin')
                ->group('Content')
                ->page()
                ->helperText('Hero blogunun en baskin mesaji.'),
            KnobDefinition::make('subheadline')
                ->label('Subheadline')
                ->text()
                ->default('Modern, esnek ve moduler icerik operasyonlari icin hazir block sistemi.')
                ->group('Content')
                ->page()
                ->helperText('Headline altindaki destekleyici aciklama.'),
            ...ActionKnobs::primaryCta(
                prefix: 'primaryCta',
                defaultText: 'Kesfet',
                defaultUrl: '/products',
            ),
            ...TypographyKnobs::alignment(),
            ...LayoutKnobs::spacing(),
        ];
    }

    public function getBlockType(): string
    {
        return 'hero-banner';
    }

    /**
     * @param  array<string, mixed>  $knobs
     * @return array<string, mixed>
     */
    public function makeBlockPayload(array $knobs, string $preset): array
    {
        return [
            'type' => $this->getBlockType(),
            'variant' => $preset,
            'version' => $this->getBlockVersion(),
            'content' => [
                'headline' => $this->normalizeText(
                    $knobs['headline'] ?? null,
                    'Struktura ile sinirlari kaldirin',
                ),
                'subheadline' => $this->normalizeText(
                    $knobs['subheadline'] ?? null,
                    'Modern, esnek ve moduler icerik operasyonlari icin hazir block sistemi.',
                ),
            ],
            'actions' => [
                'primary' => [
                    'text' => $this->normalizeText(
                        $knobs['primaryCtaText'] ?? null,
                        'Kesfet',
                    ),
                    'url' => $this->normalizeText(
                        $knobs['primaryCtaUrl'] ?? null,
                        '/products',
                    ),
                ],
            ],
            'design' => [
                'textAlign' => $this->normalizeToken(
                    $knobs['textAlign'] ?? null,
                    ['left', 'center', 'right'],
                    'center',
                ),
                'paddingTop' => $this->normalizeToken(
                    $knobs['paddingTop'] ?? null,
                    ['none', 'sm', 'md', 'lg', 'xl'],
                    'lg',
                ),
                'paddingBottom' => $this->normalizeToken(
                    $knobs['paddingBottom'] ?? null,
                    ['none', 'sm', 'md', 'lg', 'xl'],
                    'lg',
                ),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function resolveBlockData(array $payload): BlockDataContract
    {
        return HeroBannerBlockData::fromPayload($payload);
    }

    public function getFrontendView(): string
    {
        return 'filament.storybook.blocks.hero-banner';
    }

    public function presets(): array
    {
        return [
            'default' => [],
            'centered_compact' => [
                'headline' => 'Yeni sezonu tek panelden yayinlayin',
                'subheadline' => 'Editoryal bloklar, urun gridleri ve CTA akislari ayni control grammar ile yonetilir.',
                'primaryCtaText' => 'Koleksiyonu gor',
                'primaryCtaUrl' => '/collections/new-season',
                'textAlign' => 'center',
                'paddingTop' => 'md',
                'paddingBottom' => 'md',
            ],
            'left_aligned_large' => [
                'headline' => 'Kampanyalari daha hizli yayina alin',
                'subheadline' => 'Hero, grid ve ozet bloklari ayni payload kontratiyla frontend tarafina tasinir.',
                'primaryCtaText' => 'Page builderi incele',
                'primaryCtaUrl' => '/storybook',
                'textAlign' => 'left',
                'paddingTop' => 'xl',
                'paddingBottom' => 'xl',
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
[
    'type' => 'hero-banner',
    'variant' => 'default',
    'version' => 1,
    'content' => [
        'headline' => 'Struktura ile sinirlari kaldirin',
        'subheadline' => 'Modern, esnek ve moduler icerik operasyonlari icin hazir block sistemi.',
    ],
    'actions' => [
        'primary' => [
            'text' => 'Kesfet',
            'url' => '/products',
        ],
    ],
    'design' => [
        'textAlign' => 'center',
        'paddingTop' => 'lg',
        'paddingBottom' => 'lg',
    ],
]
PHP;
    }

    public function anatomy(): array
    {
        return [
            [
                'title' => 'Headline',
                'description' => 'Hero blogunun ana vaadini tek bakista anlatir.',
            ],
            [
                'title' => 'Supporting copy',
                'description' => 'Headline altinda ikincil baglam kurar ve aksiyona gecmeden once ikna eder.',
            ],
            [
                'title' => 'Primary CTA',
                'description' => 'Kullaniciya tek ana yon belirler; ikincil aksiyonlar daha sonra eklenebilir.',
            ],
            [
                'title' => 'Spacing envelope',
                'description' => 'Blok, sayfa akisi icinde ne kadar nefes alacagini layout tokenlari ile belirler.',
            ],
        ];
    }

    public function documentationSections(): array
    {
        return [
            [
                'title' => 'Typed block payload',
                'description' => 'Editor state dogrudan Bladee gitmez. Once payload, sonra DTO, en son view data olusturulur.',
                'code' => <<<'PHP'
$payload = [
    'type' => 'hero-banner',
    'content' => [...],
    'actions' => [...],
    'design' => [...],
];

$resolved = app(BlockFactory::class)->make($payload);
PHP,
                'points' => [
                    'Persist edilen veri knob state degil, normalize edilmis block payload olmalidir.',
                    'DTO katmani versioning, defaults ve sanitization icin merkezi nokta olur.',
                ],
            ],
            [
                'title' => 'Content and design separation',
                'description' => 'Headline gibi editor verileri ile alignment ve spacing gibi sunum tokenlari farkli sepetlerde tutulur.',
                'points' => [
                    'Icerik degiskenleri localisation ve editorial workflow ile daha uyumludur.',
                    'Design tokenlari ise theme veya template degisiminde daha kolay migrate edilir.',
                ],
            ],
            [
                'title' => 'Shared preview and frontend view',
                'description' => 'Storybook preview ile page builder runtime ayni Blade partiali kullanir. Fark sadece wrapper seviyesindedir.',
                'points' => [
                    'Mockup ile production markup farki azalir.',
                    'Block CSS tek yerde tutuldugunda tasarim drift etmez.',
                ],
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'default' => [
                'title' => 'Default hero',
                'description' => 'Merkez hizali, dengeli spacinge sahip genel kullanim kurgusu.',
                'code' => <<<'PHP'
$payload['design'] = [
    'textAlign' => 'center',
    'paddingTop' => 'lg',
    'paddingBottom' => 'lg',
];
PHP,
                'points' => [
                    'Landing page ve kampanya ust bandi icin guvenli baslangic varyanti.',
                    'Headline ile CTA ayni odaga toplandigi icin conversion odaklidir.',
                ],
            ],
            'centered_compact' => [
                'title' => 'Centered compact',
                'description' => 'Daha siki spacing ile liste ya da grid bloklarinin hemen ustunde kullanilacak kompakt hero.',
                'code' => <<<'PHP'
$payload['design'] = [
    'textAlign' => 'center',
    'paddingTop' => 'md',
    'paddingBottom' => 'md',
];
PHP,
                'points' => [
                    'Grid gibi yogun bloklarla daha iyi ritim kurar.',
                    'Mobilde ilk fold icine daha rahat sigar.',
                ],
            ],
            'left_aligned_large' => [
                'title' => 'Left aligned large',
                'description' => 'Editoryal veya premium vitrinlerde daha genis nefes alan, sola hizali hero.',
                'code' => <<<'PHP'
$payload['design'] = [
    'textAlign' => 'left',
    'paddingTop' => 'xl',
    'paddingBottom' => 'xl',
];
PHP,
                'points' => [
                    'Uzun copy ve hikaye anlatimi gereken sayfalarda daha dogal gorunur.',
                    'Sola hizalama, editorial ve magazin benzeri layoutlarda daha premium his verir.',
                ],
            ],
        ];
    }

    private function normalizeText(mixed $value, string $fallback): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        $value = trim($value);

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function normalizeToken(mixed $value, array $allowed, string $fallback): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            return $fallback;
        }

        return $value;
    }
}

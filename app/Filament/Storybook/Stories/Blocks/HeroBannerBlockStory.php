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

    public string $description = 'Landing page ust bandinda metin, CTA ve tek bir editorial gorseli birlikte tasiyan ana karsilama blogu.';

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
            KnobDefinition::make('imagePath')
                ->label('Hero image')
                ->file()
                ->disk('public')
                ->directory('page-blocks/hero-banners')
                ->image()
                ->default(null)
                ->group('Media')
                ->page()
                ->helperText('Public disk uzerinde saklanan tek gorsel.'),
            KnobDefinition::make('imageAlt')
                ->label('Image alt')
                ->text()
                ->default('Hero visual')
                ->group('Media')
                ->page()
                ->helperText('Erisilebilirlik ve SEO icin alternatif metin.'),
            ...TypographyKnobs::alignment(),
            ...LayoutKnobs::spacing(),
        ];
    }

    public function getBlockType(): string
    {
        return 'hero-banner';
    }

    public function supportsCmsBuilder(): bool
    {
        return false;
    }

    /**
     * @param  array<string, mixed>  $knobs
     * @return array<string, mixed>
     */
    public function makeBlockPayload(array $knobs, string $preset): array
    {
        $headline = $this->normalizeText(
            $knobs['headline'] ?? null,
            'Struktura ile sinirlari kaldirin',
        );

        return [
            'type' => $this->getBlockType(),
            'variant' => $preset,
            'version' => $this->getBlockVersion(),
            'content' => [
                'headline' => $headline,
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
            'media' => [
                'imagePath' => $this->normalizePath($knobs['imagePath'] ?? null),
                'imageAlt' => $this->normalizeText(
                    $knobs['imageAlt'] ?? null,
                    $headline,
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function makeBuilderData(array $payload): array
    {
        return [
            'headline' => $payload['content']['headline'] ?? 'Struktura ile sinirlari kaldirin',
            'subheadline' => $payload['content']['subheadline'] ?? 'Modern, esnek ve moduler icerik operasyonlari icin hazir block sistemi.',
            'primaryCtaText' => $payload['actions']['primary']['text'] ?? 'Kesfet',
            'primaryCtaUrl' => $payload['actions']['primary']['url'] ?? '/products',
            'imagePath' => is_string($payload['media']['imagePath'] ?? null)
                ? [$payload['media']['imagePath']]
                : [],
            'imageAlt' => $payload['media']['imageAlt'] ?? ($payload['content']['headline'] ?? 'Hero visual'),
            'textAlign' => $payload['design']['textAlign'] ?? 'center',
            'paddingTop' => $payload['design']['paddingTop'] ?? 'lg',
            'paddingBottom' => $payload['design']['paddingBottom'] ?? 'lg',
        ];
    }

    public function getBuilderItemLabel(?array $state = null): string
    {
        $headline = $state['headline'] ?? null;

        if (! is_string($headline)) {
            return $this->title;
        }

        $headline = trim($headline);

        return $headline !== '' ? $headline : $this->title;
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
    'media' => [
        'imagePath' => 'page-blocks/hero-banners/example.jpg',
        'imageAlt' => 'Hero visual',
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
                'title' => 'Message stack',
                'description' => 'Headline ve subheadline tek bir editorial vaadi toplar.',
            ],
            [
                'title' => 'Primary CTA',
                'description' => 'Kullaniciya tek bir ana yon verir.',
            ],
            [
                'title' => 'Hero visual',
                'description' => 'Tek bir image upload ile sayfanin tonunu belirler.',
            ],
            [
                'title' => 'Spacing envelope',
                'description' => 'Blok sayfa akisi icinde ne kadar nefes alacagini layout tokenlari ile belirler.',
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'default' => [
                'title' => 'Default hero',
                'description' => 'Merkez hizali, dengeli spacinge sahip genel kullanim kurgusu.',
                'points' => [
                    'Landing page ve kampanya ust bandi icin guvenli baslangic varyanti.',
                ],
            ],
            'centered_compact' => [
                'title' => 'Centered compact',
                'description' => 'Grid veya FAQ blogunun hemen ustunde kullanilacak daha sik hero ritmi.',
                'points' => [
                    'Mobilde fold icine daha rahat sigar.',
                ],
            ],
            'left_aligned_large' => [
                'title' => 'Left aligned large',
                'description' => 'Editoryal veya premium landing page senaryolari icin sola hizali hero.',
                'points' => [
                    'Daha uzun copy ile daha dogal gorunur.',
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

    private function normalizePath(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = array_values(array_filter(
                $value,
                static fn (mixed $item): bool => is_string($item) && trim($item) !== '',
            ))[0] ?? null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
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

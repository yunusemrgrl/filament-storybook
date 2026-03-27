<?php

namespace App\Filament\Storybook\Stories\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Filament\Storybook\Blocks\Data\ProductGridBlockData;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\Knobs\DataKnobs;
use App\Filament\Storybook\Knobs\LayoutKnobs;

class ProductGridBlockStory extends AbstractBlockStory
{
    public string $title = 'Product Grid';

    public string $group = 'Page Blocks';

    public string $icon = 'heroicon-o-squares-2x2';

    public string $description = 'Liste, kategori veya kampanya vitrini icin urun kartlarini editor kontrollu grid duzeniyle yayinlayan commerce odakli page block.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('headline')
                ->label('Headline')
                ->text()
                ->default('Featured products')
                ->group('Content')
                ->page()
                ->helperText('Grid section basligi.'),
            KnobDefinition::make('subheadline')
                ->label('Subheadline')
                ->text()
                ->default('Yeni sezondan one cikan urunleri tek blokta editor kontrollu olarak yayinlayin.')
                ->group('Content')
                ->page()
                ->helperText('Grid section aciklamasi.'),
            ...DataKnobs::listing(
                defaultItemCount: 4,
                defaultCollection: 'Spring collection',
            ),
            ...LayoutKnobs::columns(),
            ...LayoutKnobs::spacing(),
        ];
    }

    public function getBlockType(): string
    {
        return 'product-grid';
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
                    'Featured products',
                ),
                'subheadline' => $this->normalizeText(
                    $knobs['subheadline'] ?? null,
                    'Yeni sezondan one cikan urunleri tek blokta editor kontrollu olarak yayinlayin.',
                ),
            ],
            'data' => [
                'collectionLabel' => $this->normalizeText(
                    $knobs['collectionLabel'] ?? null,
                    'Spring collection',
                ),
                'itemCount' => $this->normalizeInteger(
                    $knobs['itemCount'] ?? null,
                    4,
                    2,
                    8,
                ),
                'showPrices' => (bool) ($knobs['showPrices'] ?? true),
            ],
            'design' => [
                'columns' => $this->normalizeToken(
                    $knobs['columns'] ?? null,
                    ['2', '3', '4'],
                    '4',
                ),
                'cardGap' => $this->normalizeToken(
                    $knobs['cardGap'] ?? null,
                    ['sm', 'md', 'lg'],
                    'md',
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
        return ProductGridBlockData::fromPayload($payload);
    }

    public function getFrontendView(): string
    {
        return 'filament.storybook.blocks.product-grid';
    }

    public function presets(): array
    {
        return [
            'default' => [],
            'two_column_editorial' => [
                'headline' => 'Curated essentials',
                'subheadline' => 'Daha buyuk kart ritmi ve iki kolonla editorial bir raf hissi kurar.',
                'collectionLabel' => 'Editors picks',
                'itemCount' => 4,
                'columns' => '2',
                'cardGap' => 'lg',
                'paddingTop' => 'xl',
                'paddingBottom' => 'xl',
            ],
            'dense_four_up' => [
                'headline' => 'Weekly drop',
                'subheadline' => 'Anasayfa ustunde hizli urun taramasi icin daha sik ve yogun bir grid.',
                'collectionLabel' => 'Weekly drop',
                'itemCount' => 8,
                'columns' => '4',
                'cardGap' => 'sm',
                'paddingTop' => 'md',
                'paddingBottom' => 'md',
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
[
    'type' => 'product-grid',
    'variant' => 'default',
    'version' => 1,
    'content' => [
        'headline' => 'Featured products',
        'subheadline' => 'Yeni sezondan one cikan urunleri tek blokta editor kontrollu olarak yayinlayin.',
    ],
    'data' => [
        'collectionLabel' => 'Spring collection',
        'itemCount' => 4,
        'showPrices' => true,
    ],
    'design' => [
        'columns' => '4',
        'cardGap' => 'md',
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
                'title' => 'Section copy',
                'description' => 'Grid neyi listeledigini ve neden gosterildigini aciklar.',
            ],
            [
                'title' => 'Data source hint',
                'description' => 'Collection label editore hangi veri kaynagini sectigini hatirlatir.',
            ],
            [
                'title' => 'Product cards',
                'description' => 'Gercek page builderda API veya query sonucu gelen urunler burada kart olarak render edilir.',
            ],
            [
                'title' => 'Grid density',
                'description' => 'Columns ve gap tokenlari ayni veriyi daha editorial ya da daha commerce yogun hissettirebilir.',
            ],
        ];
    }

    public function documentationSections(): array
    {
        return [
            [
                'title' => 'Content, data and design buckets',
                'description' => 'Product grid payload, page builderda en cok karisacak uc sorumlulugu ayri tutar.',
                'code' => <<<'PHP'
$payload = [
    'content' => [...],
    'data' => [
        'collectionLabel' => 'Spring collection',
        'itemCount' => 4,
    ],
    'design' => [
        'columns' => '4',
        'cardGap' => 'md',
    ],
];
PHP,
                'points' => [
                    'Content editorial ekip tarafindan degistirilir.',
                    'Data query veya relation secimine baglanir.',
                    'Design ise reusable tokenlarla tema tarafinda kontrol edilir.',
                ],
            ],
            [
                'title' => 'Preview data strategy',
                'description' => 'Storybook icinde gercek DB sorgusu yerine deterministic sample cards kullaniliyor.',
                'points' => [
                    'Editorde UI ritmi dogru gorulsun diye runtime DTO sample product listesi olusturur.',
                    'Gercek page builder runtimeinda ayni DTO, repository veya API sonucuyla beslenebilir.',
                ],
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'default' => [
                'title' => 'Default grid',
                'description' => 'Dort kolonlu, dengeli bosluklara sahip genel e-commerce vitrini.',
                'code' => <<<'PHP'
$payload['design'] = [
    'columns' => '4',
    'cardGap' => 'md',
];
PHP,
                'points' => [
                    'PLP oncesi teaser section ya da homepage raflari icin guvenli varsayim.',
                ],
            ],
            'two_column_editorial' => [
                'title' => 'Two column editorial',
                'description' => 'Daha premium ve yavas bir browse deneyimi icin buyuk kartli iki kolon grid.',
                'code' => <<<'PHP'
$payload['design'] = [
    'columns' => '2',
    'cardGap' => 'lg',
];
PHP,
                'points' => [
                    'Campaign page veya lookbook benzeri akislarda daha uygun.',
                ],
            ],
            'dense_four_up' => [
                'title' => 'Dense four-up',
                'description' => 'Daha fazla SKUyu fold icine sigdirmak isteyen yogun raf gorunumu.',
                'code' => <<<'PHP'
$payload['data']['itemCount'] = 8;
$payload['design']['cardGap'] = 'sm';
PHP,
                'points' => [
                    'Kampanya veya haftalik drop gibi hizli taranan raflarda faydali.',
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

    private function normalizeInteger(mixed $value, int $fallback, int $min, int $max): int
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        return max($min, min($max, (int) $value));
    }
}

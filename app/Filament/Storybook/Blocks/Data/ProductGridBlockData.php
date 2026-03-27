<?php

namespace App\Filament\Storybook\Blocks\Data;

use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use Illuminate\Support\Arr;

readonly class ProductGridBlockData implements BlockDataContract
{
    /**
     * @param  array<int, array{name: string, category: string, price: string}>  $products
     */
    public function __construct(
        public string $variant,
        public int $version,
        public string $headline,
        public string $subheadline,
        public string $collectionLabel,
        public string $columns,
        public string $cardGap,
        public string $paddingTop,
        public string $paddingBottom,
        public bool $showPrices,
        public array $products,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static
    {
        $columns = self::normalizeToken(
            Arr::get($payload, 'design.columns'),
            ['2', '3', '4'],
            '4',
        );

        $itemCount = self::normalizeInteger(
            Arr::get($payload, 'data.itemCount'),
            4,
            2,
            8,
        );

        return new static(
            variant: self::normalizeText($payload['variant'] ?? null, 'default'),
            version: self::normalizeInteger($payload['version'] ?? null, 1, 1, 999),
            headline: self::normalizeText(
                Arr::get($payload, 'content.headline'),
                'Featured products',
            ),
            subheadline: self::normalizeText(
                Arr::get($payload, 'content.subheadline'),
                'Yeni sezondan one cikan urunleri tek blokta editor kontrollu olarak yayinlayin.',
            ),
            collectionLabel: self::normalizeText(
                Arr::get($payload, 'data.collectionLabel'),
                'Spring collection',
            ),
            columns: $columns,
            cardGap: self::normalizeToken(
                Arr::get($payload, 'design.cardGap'),
                ['sm', 'md', 'lg'],
                'md',
            ),
            paddingTop: self::normalizeToken(
                Arr::get($payload, 'design.paddingTop'),
                ['none', 'sm', 'md', 'lg', 'xl'],
                'lg',
            ),
            paddingBottom: self::normalizeToken(
                Arr::get($payload, 'design.paddingBottom'),
                ['none', 'sm', 'md', 'lg', 'xl'],
                'lg',
            ),
            showPrices: (bool) Arr::get($payload, 'data.showPrices', true),
            products: self::sampleProducts($itemCount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'product-grid',
            'variant' => $this->variant,
            'version' => $this->version,
            'content' => [
                'headline' => $this->headline,
                'subheadline' => $this->subheadline,
            ],
            'data' => [
                'collectionLabel' => $this->collectionLabel,
                'itemCount' => count($this->products),
                'showPrices' => $this->showPrices,
            ],
            'design' => [
                'columns' => $this->columns,
                'cardGap' => $this->cardGap,
                'paddingTop' => $this->paddingTop,
                'paddingBottom' => $this->paddingBottom,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        return [
            'block' => $this,
        ];
    }

    public function wrapperClasses(): string
    {
        return implode(' ', [
            "is-pt-{$this->paddingTop}",
            "is-pb-{$this->paddingBottom}",
        ]);
    }

    public function gridClasses(): string
    {
        return implode(' ', [
            "is-cols-{$this->columns}",
            "is-gap-{$this->cardGap}",
        ]);
    }

    /**
     * @param  array<int, array{name: string, category: string, price: string}>  $products
     * @return array<int, array{name: string, category: string, price: string}>
     */
    private static function sampleProducts(int $itemCount): array
    {
        return array_slice([
            ['name' => 'Aster Leather Tote', 'category' => 'Bags', 'price' => '$148'],
            ['name' => 'Luma Cashmere Knit', 'category' => 'Knitwear', 'price' => '$112'],
            ['name' => 'Forma Travel Bottle', 'category' => 'Accessories', 'price' => '$36'],
            ['name' => 'Sora Studio Chair', 'category' => 'Home', 'price' => '$260'],
            ['name' => 'Nami Ceramic Set', 'category' => 'Dining', 'price' => '$82'],
            ['name' => 'Axis Weekend Jacket', 'category' => 'Outerwear', 'price' => '$178'],
            ['name' => 'Miro Running Cap', 'category' => 'Sport', 'price' => '$28'],
            ['name' => 'Vela Travel Pouch', 'category' => 'Organizers', 'price' => '$54'],
        ], 0, $itemCount);
    }

    private static function normalizeText(mixed $value, string $fallback): string
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
    private static function normalizeToken(mixed $value, array $allowed, string $fallback): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            return $fallback;
        }

        return $value;
    }

    private static function normalizeInteger(mixed $value, int $fallback, int $min, int $max): int
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        return max($min, min($max, (int) $value));
    }
}

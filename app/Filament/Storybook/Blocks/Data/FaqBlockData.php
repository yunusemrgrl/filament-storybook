<?php

namespace App\Filament\Storybook\Blocks\Data;

use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use Illuminate\Support\Arr;

readonly class FaqBlockData implements BlockDataContract
{
    /**
     * @param  array<int, array{question: string, answer: string}>  $items
     */
    public function __construct(
        public string $variant,
        public int $version,
        public string $sectionTitle,
        public string $introText,
        public string $paddingTop,
        public string $paddingBottom,
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new static(
            variant: self::normalizeText($payload['variant'] ?? null, 'default'),
            version: self::normalizeInteger($payload['version'] ?? null, 1, 1, 999),
            sectionTitle: self::normalizeText(
                Arr::get($payload, 'content.sectionTitle'),
                'Frequently asked questions',
            ),
            introText: self::normalizeText(
                Arr::get($payload, 'content.introText'),
                'Siparis, teslimat ve iade surecini tek blokta aciklayin.',
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
            items: self::normalizeItems(Arr::get($payload, 'data.items')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'faq',
            'variant' => $this->variant,
            'version' => $this->version,
            'content' => [
                'sectionTitle' => $this->sectionTitle,
                'introText' => $this->introText,
            ],
            'data' => [
                'items' => $this->items,
            ],
            'design' => [
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

    public function hasIntro(): bool
    {
        return $this->introText !== '';
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

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private static function normalizeItems(mixed $items): array
    {
        if (! is_array($items)) {
            return self::defaultItems();
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = self::normalizeText($item['question'] ?? null, '');
            $answer = self::normalizeText($item['answer'] ?? null, '');

            if ($question === '' || $answer === '') {
                continue;
            }

            $normalized[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $normalized !== [] ? $normalized : self::defaultItems();
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private static function defaultItems(): array
    {
        return [
            [
                'question' => 'Siparisim ne zaman kargoya verilir?',
                'answer' => 'Hafta ici verilen siparisler ayni gun icinde, yogun donemlerde en gec ertesi is gunu kargolanir.',
            ],
            [
                'question' => 'Iade suresi kac gun?',
                'answer' => 'Teslimattan itibaren 14 gun icinde iade talebi olusturabilirsiniz.',
            ],
            [
                'question' => 'Uluslararasi gonderim var mi?',
                'answer' => 'Evet, secili bolgeler icin uluslararasi teslimat aktif ve ucretler checkout adiminda hesaplanir.',
            ],
        ];
    }
}

<?php

namespace App\Filament\Storybook\Blocks\Data;

use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class HeroBannerBlockData implements BlockDataContract
{
    public function __construct(
        public string $variant,
        public int $version,
        public string $headline,
        public string $subheadline,
        public string $primaryCtaText,
        public string $primaryCtaUrl,
        public string $textAlign,
        public string $paddingTop,
        public string $paddingBottom,
        public ?string $imagePath,
        public string $imageAlt,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static
    {
        $headline = self::normalizeText(
            Arr::get($payload, 'content.headline'),
            'Struktura ile sinirlari kaldirin',
        );

        return new static(
            variant: self::normalizeVariant($payload['variant'] ?? null),
            version: self::normalizeVersion($payload['version'] ?? null),
            headline: $headline,
            subheadline: self::normalizeText(
                Arr::get($payload, 'content.subheadline'),
                'Modern, moduler ve hizli icerik operasyonlari icin page builder tabanli vitrin bloklari olusturun.',
            ),
            primaryCtaText: self::normalizeText(
                Arr::get($payload, 'actions.primary.text'),
                'Kesfet',
            ),
            primaryCtaUrl: self::normalizeUrl(
                Arr::get($payload, 'actions.primary.url'),
            ),
            textAlign: self::normalizeToken(
                Arr::get($payload, 'design.textAlign'),
                ['left', 'center', 'right'],
                'center',
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
            imagePath: self::normalizePath(Arr::get($payload, 'media.imagePath')),
            imageAlt: self::normalizeText(
                Arr::get($payload, 'media.imageAlt'),
                $headline,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'hero-banner',
            'variant' => $this->variant,
            'version' => $this->version,
            'content' => [
                'headline' => $this->headline,
                'subheadline' => $this->subheadline,
            ],
            'actions' => [
                'primary' => [
                    'text' => $this->primaryCtaText,
                    'url' => $this->primaryCtaUrl,
                ],
            ],
            'design' => [
                'textAlign' => $this->textAlign,
                'paddingTop' => $this->paddingTop,
                'paddingBottom' => $this->paddingBottom,
            ],
            'media' => [
                'imagePath' => $this->imagePath,
                'imageAlt' => $this->imageAlt,
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

    public function contentClasses(): string
    {
        return "is-align-{$this->textAlign}";
    }

    public function hasPrimaryAction(): bool
    {
        return $this->primaryCtaText !== '';
    }

    public function hasImage(): bool
    {
        return $this->imagePath !== null;
    }

    public function imageUrl(): ?string
    {
        if ($this->imagePath === null) {
            return null;
        }

        return Storage::disk('public')->url($this->imagePath);
    }

    private static function normalizeVariant(mixed $value): string
    {
        return is_string($value) && $value !== '' ? $value : 'default';
    }

    private static function normalizeVersion(mixed $value): int
    {
        return is_numeric($value) ? max(1, (int) $value) : 1;
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

    private static function normalizeUrl(mixed $value): string
    {
        if (! is_string($value)) {
            return '#';
        }

        $value = trim($value);

        if ($value === '') {
            return '#';
        }

        if (Str::startsWith($value, ['#', '/', 'http://', 'https://', 'mailto:', 'tel:'])) {
            return $value;
        }

        return '/'.ltrim($value, '/');
    }

    private static function normalizePath(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}

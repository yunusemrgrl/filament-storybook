<?php

namespace App\Filament\Storybook\Stories\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Filament\Storybook\Blocks\Data\FaqBlockData;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\Knobs\LayoutKnobs;

class FaqBlockStory extends AbstractBlockStory
{
    public string $title = 'FAQ';

    public string $group = 'Page Blocks';

    public string $icon = 'heroicon-o-chat-bubble-left-right';

    public string $description = 'SSS, teslimat notlari ve operasyonel aciklamalari tekrarli soru-cevap verisiyle yayinlayan repeater tabanli page block.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('sectionTitle')
                ->label('Section title')
                ->text()
                ->default('Frequently asked questions')
                ->group('Content')
                ->page()
                ->helperText('Blok ustunde gorunen baslik.'),
            KnobDefinition::make('introText')
                ->label('Intro text')
                ->text()
                ->default('Siparis, teslimat ve iade surecini tek blokta aciklayin.')
                ->group('Content')
                ->page()
                ->helperText('Basligin altindaki kisa aciklama.'),
            KnobDefinition::make('items')
                ->label('FAQ items')
                ->repeater([
                    KnobDefinition::make('question')
                        ->label('Question')
                        ->text()
                        ->default(''),
                    KnobDefinition::make('answer')
                        ->label('Answer')
                        ->text()
                        ->default(''),
                ])
                ->default([
                    [
                        'question' => 'Siparisim ne zaman kargoya verilir?',
                        'answer' => 'Hafta ici verilen siparisler ayni gun icinde, yogun donemlerde en gec ertesi is gunu kargolanir.',
                    ],
                    [
                        'question' => 'Iade suresi kac gun?',
                        'answer' => 'Teslimattan itibaren 14 gun icinde iade talebi olusturabilirsiniz.',
                    ],
                ])
                ->group('Content')
                ->page()
                ->repeaterItemLabelField('question')
                ->repeaterAddActionLabel('Add FAQ item')
                ->minItems(1)
                ->helperText('Question ve answer alanlarini tekrarli JSON liste olarak tutar.'),
            ...LayoutKnobs::spacing(),
        ];
    }

    public function getBlockType(): string
    {
        return 'faq';
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
        return [
            'type' => $this->getBlockType(),
            'variant' => $preset,
            'version' => $this->getBlockVersion(),
            'content' => [
                'sectionTitle' => $this->normalizeText(
                    $knobs['sectionTitle'] ?? null,
                    'Frequently asked questions',
                ),
                'introText' => $this->normalizeText(
                    $knobs['introText'] ?? null,
                    'Siparis, teslimat ve iade surecini tek blokta aciklayin.',
                ),
            ],
            'data' => [
                'items' => $this->normalizeItems($knobs['items'] ?? null),
            ],
            'design' => [
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
        return FaqBlockData::fromPayload($payload);
    }

    public function getFrontendView(): string
    {
        return 'filament.storybook.blocks.faq';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function makeBuilderData(array $payload): array
    {
        return [
            'sectionTitle' => $payload['content']['sectionTitle'] ?? 'Frequently asked questions',
            'introText' => $payload['content']['introText'] ?? 'Siparis, teslimat ve iade surecini tek blokta aciklayin.',
            'items' => $payload['data']['items'] ?? [],
            'paddingTop' => $payload['design']['paddingTop'] ?? 'lg',
            'paddingBottom' => $payload['design']['paddingBottom'] ?? 'lg',
        ];
    }

    public function getBuilderItemLabel(?array $state = null): string
    {
        $title = $state['sectionTitle'] ?? null;

        if (! is_string($title)) {
            return $this->title;
        }

        $title = trim($title);

        return $title !== '' ? $title : $this->title;
    }

    public function presets(): array
    {
        return [
            'default' => [],
            'compact_support' => [
                'sectionTitle' => 'Yardim ve teslimat',
                'introText' => 'Teslimat, iade ve siparis takip konularinda en sik sorulan basliklar.',
                'paddingTop' => 'md',
                'paddingBottom' => 'md',
            ],
        ];
    }

    public function getUsageSnippet(): ?string
    {
        return <<<'PHP'
[
    'type' => 'faq',
    'variant' => 'default',
    'version' => 1,
    'content' => [
        'sectionTitle' => 'Frequently asked questions',
        'introText' => 'Siparis, teslimat ve iade surecini tek blokta aciklayin.',
    ],
    'data' => [
        'items' => [
            ['question' => '...', 'answer' => '...'],
        ],
    ],
]
PHP;
    }

    public function anatomy(): array
    {
        return [
            [
                'title' => 'Section copy',
                'description' => 'Baslik ve intro text, tekrarli listeye editorial baglam ekler.',
            ],
            [
                'title' => 'Repeater payload',
                'description' => 'Her item question ve answer alanlarindan olusan normalize bir JSON entrysidir.',
            ],
            [
                'title' => 'Operational reuse',
                'description' => 'Ayni block teslimat, iade, odeme ve onboarding anlatilari icin yeniden kullanilabilir.',
            ],
        ];
    }

    public function presetDocs(): array
    {
        return [
            'default' => [
                'title' => 'Default FAQ',
                'description' => 'Genel destek ve operasyonel aciklama sayfalari icin dengeli spacinge sahip temel kurgu.',
            ],
            'compact_support' => [
                'title' => 'Compact support',
                'description' => 'Hero ya da checkout ozetinden sonra daha siki ritimle kullanilan destek blogu.',
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
     * @return array<int, array{question: string, answer: string}>
     */
    private function normalizeItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = $this->normalizeText($item['question'] ?? null, '');
            $answer = $this->normalizeText($item['answer'] ?? null, '');

            if ($question === '' || $answer === '') {
                continue;
            }

            $normalized[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $normalized;
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

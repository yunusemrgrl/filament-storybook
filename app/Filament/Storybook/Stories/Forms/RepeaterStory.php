<?php

namespace App\Filament\Storybook\Stories\Forms;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class RepeaterStory extends AbstractFormStory
{
    public string $title = 'Repeater';

    public string $group = 'Forms';

    public string $icon = 'heroicon-o-bars-3-bottom-left';

    public string $description = 'Repeater, tekrar eden JSON item listeleri olusturarak FAQ, link listesi ve kucuk collection editorlerini kurar.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('label')->label('label')->text()->default('FAQ items')->group('Content'),
            KnobDefinition::make('addActionLabel')->label('addActionLabel')->text()->default('Add item')->group('Behavior'),
            KnobDefinition::make('itemLabelField')->label('itemLabelField')->select([
                'question' => 'Question',
                'answer' => 'Answer',
            ])->default('question')->group('Behavior'),
        ];
    }

    public function build(array $knobs): Repeater
    {
        $field = Repeater::make('preview')
            ->label((string) ($knobs['label'] ?? 'FAQ items'))
            ->schema([
                TextInput::make('question')->label('Question')->required(),
                TextInput::make('answer')->label('Answer')->required(),
            ])
            ->itemLabel(function (array $state) use ($knobs): ?string {
                $key = is_string($knobs['itemLabelField'] ?? null) ? $knobs['itemLabelField'] : 'question';
                $value = $state[$key] ?? null;

                if (! is_string($value)) {
                    return null;
                }

                $value = trim($value);

                return $value !== '' ? $value : null;
            });

        if ($addActionLabel = $this->normalizeString($knobs['addActionLabel'] ?? null)) {
            $field->addActionLabel($addActionLabel);
        }

        return $field;
    }

    public function presetPreviewData(): array
    {
        return [
            'default' => [
                'preview' => [
                    [
                        'question' => 'Kargo suresi ne kadar?',
                        'answer' => 'Cogu siparis ayni gun kargoya verilir.',
                    ],
                    [
                        'question' => 'Iade kabul ediyor musunuz?',
                        'answer' => 'Teslimattan itibaren 14 gun icinde iade kabul edilir.',
                    ],
                ],
            ],
        ];
    }

    public function getExternalDocsUrl(): ?string
    {
        return 'https://filamentphp.com/docs/5.x/forms/repeater';
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}

<?php

namespace App\Filament\Storybook\Stories\Forms;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use Filament\Forms\Components\FileUpload;

class FileUploadStory extends AbstractFormStory
{
    public string $title = 'FileUpload';

    public string $group = 'Forms';

    public string $icon = 'heroicon-o-arrow-up-tray';

    public string $description = 'FileUpload, local disk veya cloud storage hedeflerine dosya yukleyerek image ve belge alanlarini tek API ile kurar.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('label')->label('label')->text()->default('Hero image')->group('Content'),
            KnobDefinition::make('helperText')->label('helperText')->text()->default('Tek bir gorsel secin.')->group('Content'),
            KnobDefinition::make('image')->label('image')->boolean()->default(true)->group('Behavior'),
            KnobDefinition::make('multiple')->label('multiple')->boolean()->default(false)->group('Behavior'),
        ];
    }

    public function build(array $knobs): FileUpload
    {
        $field = FileUpload::make('preview')
            ->label((string) ($knobs['label'] ?? 'Hero image'))
            ->disk('public')
            ->directory('storybook/uploads');

        if (($knobs['image'] ?? true) === true) {
            $field->image();
        }

        if (($knobs['multiple'] ?? false) === true) {
            $field->multiple();
        }

        if ($helperText = $this->normalizeString($knobs['helperText'] ?? null)) {
            $field->helperText($helperText);
        }

        return $field;
    }

    public function presets(): array
    {
        return [
            'image' => [],
            'document' => [
                'label' => 'Attachment',
                'helperText' => 'Belge veya dosya yukleyin.',
                'image' => false,
            ],
        ];
    }

    public function getExternalDocsUrl(): ?string
    {
        return 'https://filamentphp.com/docs/5.x/forms/file-upload';
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

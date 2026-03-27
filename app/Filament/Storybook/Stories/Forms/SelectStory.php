<?php

namespace App\Filament\Storybook\Stories\Forms;

use App\Filament\Storybook\AbstractFormStory;
use App\Filament\Storybook\KnobDefinition;
use Filament\Forms\Components\Select;

class SelectStory extends AbstractFormStory
{
    public string $title = 'Select';

    public string $group = 'Forms';

    public string $icon = 'heroicon-o-chevron-up-down';

    public string $description = 'Select, sabit secenekler, arama ve multi-select davranisini tek bir dropdown API uzerinden yonetir.';

    public function knobs(): array
    {
        return [
            KnobDefinition::make('label')->label('label')->text()->default('Status')->group('Content'),
            KnobDefinition::make('helperText')->label('helperText')->text()->default('Bir secenek secin.')->group('Content'),
            KnobDefinition::make('placeholder')->label('placeholder')->text()->default('Seciniz')->group('Content'),
            KnobDefinition::make('required')->label('required')->boolean()->default(false)->group('State'),
            KnobDefinition::make('searchable')->label('searchable')->boolean()->default(false)->group('Behavior'),
            KnobDefinition::make('multiple')->label('multiple')->boolean()->default(false)->group('Behavior'),
            KnobDefinition::make('optionSet')->label('optionSet')->select([
                'status' => 'Status',
                'team' => 'Team',
            ])->default('status')->group('Data'),
        ];
    }

    public function build(array $knobs): Select
    {
        $field = Select::make('preview')
            ->label((string) ($knobs['label'] ?? 'Status'))
            ->options($this->getOptions((string) ($knobs['optionSet'] ?? 'status')))
            ->live();

        if (($knobs['required'] ?? false) === true) {
            $field->required();
        }

        if (($knobs['searchable'] ?? false) === true) {
            $field->searchable();
        }

        if (($knobs['multiple'] ?? false) === true) {
            $field->multiple();
        }

        if ($placeholder = $this->normalizeString($knobs['placeholder'] ?? null)) {
            $field->placeholder($placeholder);
        }

        if ($helperText = $this->normalizeString($knobs['helperText'] ?? null)) {
            $field->helperText($helperText);
        }

        return $field;
    }

    public function presets(): array
    {
        return [
            'default' => [],
            'searchable' => [
                'label' => 'Team',
                'helperText' => 'Uzun listelerde arama acilir.',
                'searchable' => true,
                'optionSet' => 'team',
            ],
            'multiple' => [
                'label' => 'Teams',
                'helperText' => 'Birden fazla secim yapilabilir.',
                'multiple' => true,
                'searchable' => true,
                'optionSet' => 'team',
            ],
        ];
    }

    public function presetPreviewData(): array
    {
        return [
            'default' => ['preview' => 'draft'],
            'searchable' => ['preview' => 'growth'],
            'multiple' => ['preview' => ['growth', 'ops']],
        ];
    }

    public function getExternalDocsUrl(): ?string
    {
        return 'https://filamentphp.com/docs/5.x/forms/select';
    }

    /**
     * @return array<string, string>
     */
    private function getOptions(string $optionSet): array
    {
        return match ($optionSet) {
            'team' => [
                'design' => 'Design',
                'growth' => 'Growth',
                'ops' => 'Operations',
                'support' => 'Support',
            ],
            default => [
                'draft' => 'Draft',
                'review' => 'In review',
                'published' => 'Published',
            ],
        };
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

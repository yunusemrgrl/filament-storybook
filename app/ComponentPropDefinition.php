<?php

namespace App;

use App\Filament\Storybook\KnobDefinition;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class ComponentPropDefinition implements Arrayable
{
    /**
     * @param  array<int, array{value: string, label: string}>  $options
     */
    public function __construct(
        public string $name,
        public string $label,
        public ComponentPropType $type,
        public string $group = 'Content',
        public ?string $helperText = null,
        public bool $required = false,
        public array $options = [],
        public ?ComponentPropDefinitionCollection $fields = null,
        public string $disk = 'public',
        public string $directory = 'page-builder/uploads',
        public bool $image = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rawType = is_string($data['type'] ?? null)
            ? $data['type']
            : ComponentPropType::Text->value;

        $type = ComponentPropType::tryFrom($rawType) ?? ComponentPropType::Text;
        $name = Str::of((string) ($data['name'] ?? 'field'))
            ->trim()
            ->snake()
            ->replace('-', '_')
            ->value();

        if ($name === '') {
            $name = 'field';
        }

        $label = trim((string) ($data['label'] ?? ''));
        $group = trim((string) ($data['group'] ?? ''));
        $helperText = trim((string) ($data['helper_text'] ?? ''));

        return new self(
            name: $name,
            label: $label !== '' ? $label : Str::headline($name),
            type: $type,
            group: $group !== '' ? $group : 'Content',
            helperText: $helperText !== '' ? $helperText : null,
            required: (bool) ($data['required'] ?? false),
            options: self::normalizeOptions($data['options'] ?? []),
            fields: $type === ComponentPropType::Repeater
                ? ComponentPropDefinitionCollection::fromArray($data['fields'] ?? [])
                : null,
            disk: self::normalizeString($data['disk'] ?? null, 'public'),
            directory: self::normalizeString($data['directory'] ?? null, 'page-builder/uploads'),
            image: (bool) ($data['image'] ?? false),
        );
    }

    public function toKnobDefinition(): KnobDefinition
    {
        $definition = KnobDefinition::make($this->name)
            ->label($this->label)
            ->group($this->group)
            ->required($this->required);

        if ($this->helperText !== null) {
            $definition->helperText($this->helperText);
        }

        return match ($this->type) {
            ComponentPropType::Boolean => $definition->boolean(),
            ComponentPropType::Number => $definition->number(),
            ComponentPropType::Select => $definition->select($this->optionsForSelect()),
            ComponentPropType::File => $definition
                ->file()
                ->disk($this->disk)
                ->directory($this->directory)
                ->image($this->image),
            ComponentPropType::Repeater => $definition
                ->repeater($this->fields?->toKnobDefinitions() ?? [])
                ->repeaterItemLabelField($this->fields?->firstNamed('title')?->name
                    ?? $this->fields?->firstNamed('headline')?->name
                    ?? $this->fields?->first()?->name
                    ?? 'item'),
            default => $definition->text(),
        };
    }

    public function normalizeValue(mixed $value): mixed
    {
        return match ($this->type) {
            ComponentPropType::Boolean => (bool) $value,
            ComponentPropType::Number => $this->normalizeNumberValue($value),
            ComponentPropType::Select => $this->normalizeSelectValue($value),
            ComponentPropType::File => $this->normalizeFileValue($value),
            ComponentPropType::Repeater => $this->normalizeRepeaterValue($value),
            default => $this->normalizeTextValue($value),
        };
    }

    public function makeBuilderValue(mixed $value): mixed
    {
        return match ($this->type) {
            ComponentPropType::File => $this->normalizeBuilderFileValue($value),
            ComponentPropType::Repeater => $this->normalizeBuilderRepeaterValue($value),
            default => $this->normalizeValue($value),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type->value,
            'group' => $this->group,
            'helper_text' => $this->helperText,
            'required' => $this->required,
            'options' => $this->options,
            'fields' => $this->fields?->toArray() ?? [],
            'disk' => $this->disk,
            'directory' => $this->directory,
            'image' => $this->image,
        ];
    }

    private function normalizeTextValue(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }

    private function normalizeNumberValue(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeSelectValue(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = trim($value);

        return array_key_exists($value, $this->optionsForSelect()) ? $value : '';
    }

    private function normalizeFileValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = Arr::first(array_filter(
                $value,
                static fn (mixed $item): bool => is_string($item) && trim($item) !== '',
            ));
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRepeaterValue(mixed $value): array
    {
        if (! is_array($value) || $this->fields === null) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $item): array => $this->fields->normalizeValues(is_array($item) ? $item : []),
            array_filter($value, 'is_array'),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function normalizeBuilderFileValue(mixed $value): array
    {
        $normalized = $this->normalizeFileValue($value);

        return $normalized !== null ? [$normalized] : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBuilderRepeaterValue(mixed $value): array
    {
        if (! is_array($value) || $this->fields === null) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $item): array => $this->fields->makeBuilderData(is_array($item) ? $item : []),
            array_filter($value, 'is_array'),
        ));
    }

    private static function normalizeString(mixed $value, string $fallback): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        $value = trim($value);

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private static function normalizeOptions(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $normalized = [];

        foreach ($options as $option) {
            if (! is_array($option)) {
                continue;
            }

            $value = trim((string) ($option['value'] ?? ''));
            $label = trim((string) ($option['label'] ?? ''));

            if ($value === '' || $label === '') {
                continue;
            }

            $normalized[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    private function optionsForSelect(): array
    {
        $options = [];

        foreach ($this->options as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}

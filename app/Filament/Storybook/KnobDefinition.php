<?php

namespace App\Filament\Storybook;

class KnobDefinition
{
    public const TYPE_TEXT = 'text';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_SELECT = 'select';

    public const TYPE_NUMBER = 'number';

    public const TYPE_FILE = 'file';

    public const TYPE_REPEATER = 'repeater';

    public const LEVEL_PROTOTYPE = 'prototype';

    public const LEVEL_COMPONENT = 'component';

    public const LEVEL_PAGE = 'page';

    private string $name;

    private string $label;

    private string $type = self::TYPE_TEXT;

    /**
     * @var array<string, string>
     */
    private array $options = [];

    private mixed $default = null;

    private ?string $helperText = null;

    private string $group = 'General';

    private string $level = self::LEVEL_PROTOTYPE;

    /**
     * @var array<int, string>
     */
    private array $supports = [];

    private bool $required = false;

    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    private function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucwords((string) preg_replace('/([A-Z])/', ' $1', $name));
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function text(): static
    {
        $this->type = self::TYPE_TEXT;

        return $this;
    }

    public function boolean(): static
    {
        $this->type = self::TYPE_BOOLEAN;

        return $this;
    }

    /**
     * @param  array<int|string, string>  $options
     */
    public function select(array $options): static
    {
        $this->type = self::TYPE_SELECT;
        $this->options = array_is_list($options)
            ? array_combine($options, array_map('ucfirst', $options))
            : $options;

        return $this;
    }

    public function number(): static
    {
        $this->type = self::TYPE_NUMBER;

        return $this;
    }

    public function file(): static
    {
        $this->type = self::TYPE_FILE;

        return $this;
    }

    /**
     * @param  array<int, KnobDefinition>  $itemSchema
     */
    public function repeater(array $itemSchema): static
    {
        $this->type = self::TYPE_REPEATER;
        $this->meta['schema'] = $itemSchema;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function helperText(string $text): static
    {
        $this->helperText = $text;

        return $this;
    }

    public function group(string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function level(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function prototype(): static
    {
        return $this->level(self::LEVEL_PROTOTYPE);
    }

    public function component(): static
    {
        return $this->level(self::LEVEL_COMPONENT);
    }

    public function page(): static
    {
        return $this->level(self::LEVEL_PAGE);
    }

    /**
     * @param  array<int, string>  $presets
     */
    public function supports(array $presets): static
    {
        $this->supports = array_values(array_unique($presets));

        return $this;
    }

    public function required(bool $condition = true): static
    {
        $this->required = $condition;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->meta['disk'] = $disk;

        return $this;
    }

    public function directory(string $directory): static
    {
        $this->meta['directory'] = $directory;

        return $this;
    }

    public function image(bool $condition = true): static
    {
        $this->meta['image'] = $condition;

        return $this;
    }

    public function repeaterItemLabelField(string $field): static
    {
        $this->meta['itemLabelField'] = $field;

        return $this;
    }

    public function repeaterAddActionLabel(string $label): static
    {
        $this->meta['addActionLabel'] = $label;

        return $this;
    }

    public function minItems(int $count): static
    {
        $this->meta['minItems'] = $count;

        return $this;
    }

    public function maxItems(int $count): static
    {
        $this->meta['maxItems'] = $count;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getHelperText(): ?string
    {
        return $this->helperText;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getLevelLabel(): string
    {
        return match ($this->level) {
            self::LEVEL_COMPONENT => 'Component',
            self::LEVEL_PAGE => 'Page',
            default => 'Prototype',
        };
    }

    /**
     * @return array<int, string>
     */
    public function getSupports(): array
    {
        return $this->supports;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function supportsPreset(string $preset): bool
    {
        if ($this->supports === []) {
            return true;
        }

        return in_array($preset, $this->supports, true);
    }

    /**
     * @return array<int, KnobDefinition>
     */
    public function getRepeaterSchema(): array
    {
        return $this->meta['schema'] ?? [];
    }

    public function getFileDisk(): ?string
    {
        return $this->meta['disk'] ?? null;
    }

    public function getFileDirectory(): ?string
    {
        return $this->meta['directory'] ?? null;
    }

    public function isImageFile(): bool
    {
        return (bool) ($this->meta['image'] ?? false);
    }

    public function getRepeaterItemLabelField(): ?string
    {
        return $this->meta['itemLabelField'] ?? null;
    }

    public function getRepeaterAddActionLabel(): ?string
    {
        return $this->meta['addActionLabel'] ?? null;
    }

    public function getMinItems(): ?int
    {
        return $this->meta['minItems'] ?? null;
    }

    public function getMaxItems(): ?int
    {
        return $this->meta['maxItems'] ?? null;
    }
}

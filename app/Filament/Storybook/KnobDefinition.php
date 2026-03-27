<?php

namespace App\Filament\Storybook;

/**
 * KnobDefinition
 *
 * Tek bir knob'u tanımlayan value object.
 * "Bu component'in şu prop'u, şu tip kontrol ile, şu default değerle düzenlenebilir"
 * bilgisini taşır.
 *
 * KULLANIM:
 * KnobDefinition::make('label')->text()->default('Name')
 * KnobDefinition::make('disabled')->boolean()->default(false)
 * KnobDefinition::make('color')->select(['primary','danger','warning'])->default('primary')
 * KnobDefinition::make('maxLength')->number()->default(255)
 *
 * NEDEN VALUE OBJECT?
 * Knob tanımı sadece veri taşır, davranışı yoktur.
 * FormStoryRenderer bu objeyi okuyarak sağ paneldeki
 * kontrol formunu otomatik oluşturur.
 */
class KnobDefinition
{
    // Desteklenen knob tipleri
    // Her tip sağ panelde farklı bir Filament field olarak render edilir:
    // text    → TextInput
    // boolean → Toggle
    // select  → Select
    // number  → TextInput (numeric)
    // color   → ColorPicker (Aşama 3+)
    public const TYPE_TEXT = 'text';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_SELECT = 'select';

    public const TYPE_NUMBER = 'number';

    public const LEVEL_PROTOTYPE = 'prototype';

    public const LEVEL_COMPONENT = 'component';

    public const LEVEL_PAGE = 'page';

    /** build() metoduna geçilecek array key'i */
    private string $name;

    /** Sağ panelde gösterilecek label */
    private string $label;

    /** Knob tipi */
    private string $type = self::TYPE_TEXT;

    /** Select tipinde seçenekler */
    private array $options = [];

    /** Başlangıç değeri */
    private mixed $default = null;

    /** Açıklama metni (sağ panelde field altında gösterilir) */
    private ?string $helperText = null;

    /** Sağ panelde hangi bölüm altında listeleneceği */
    private string $group = 'General';

    /** Knob'un tasarım sistemi katmanındaki seviyesi */
    private string $level = self::LEVEL_PROTOTYPE;

    /**
     * Bu knob'un hangi preset'lerde görünmesi gerektiği.
     *
     * @var array<int, string>
     */
    private array $supports = [];

    // -------------------------------------------------------------------------
    // Constructor & factory
    // -------------------------------------------------------------------------

    private function __construct(string $name)
    {
        $this->name = $name;
        // Label default olarak name'den üretilir: 'maxLength' → 'Max Length'
        $this->label = ucwords(preg_replace('/([A-Z])/', ' $1', $name));
    }

    /**
     * Yeni bir KnobDefinition oluşturur.
     * Her zaman bu static factory method ile başlanır.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    // -------------------------------------------------------------------------
    // Tip belirleyiciler (fluent API)
    // -------------------------------------------------------------------------

    /**
     * Sağ panelde TextInput olarak render edilir.
     * Label, placeholder, hint, helperText gibi string prop'lar için.
     */
    public function text(): static
    {
        $this->type = self::TYPE_TEXT;

        return $this;
    }

    /**
     * Sağ panelde Toggle olarak render edilir.
     * disabled, required, readonly, revealable gibi boolean prop'lar için.
     */
    public function boolean(): static
    {
        $this->type = self::TYPE_BOOLEAN;

        return $this;
    }

    /**
     * Sağ panelde Select olarak render edilir.
     * Sabit seçenekler arasından bir değer seçmek için.
     *
     * @param  array  $options  ['value' => 'Label'] veya ['value1', 'value2']
     */
    public function select(array $options): static
    {
        $this->type = self::TYPE_SELECT;
        // ['sm', 'md', 'lg'] → ['sm' => 'Sm', 'md' => 'Md', 'lg' => 'Lg']
        // ['primary' => 'Primary'] formatındaysa olduğu gibi kullan
        $this->options = array_is_list($options)
            ? array_combine($options, array_map('ucfirst', $options))
            : $options;

        return $this;
    }

    /**
     * Sağ panelde numeric TextInput olarak render edilir.
     * minLength, maxLength, columns gibi integer prop'lar için.
     */
    public function number(): static
    {
        $this->type = self::TYPE_NUMBER;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Diğer ayarlar (fluent API)
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Getters (FormStoryRenderer tarafından okunur)
    // -------------------------------------------------------------------------

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

    public function supportsPreset(string $preset): bool
    {
        if ($this->supports === []) {
            return true;
        }

        return in_array($preset, $this->supports, true);
    }
}

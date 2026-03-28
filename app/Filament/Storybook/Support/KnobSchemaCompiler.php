<?php

namespace App\Filament\Storybook\Support;

use App\Filament\Forms\Components\NativeFileUpload;
use App\Filament\Storybook\KnobDefinition;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

class KnobSchemaCompiler
{
    /**
     * @param  array<int, KnobDefinition>  $definitions
     * @return array<int, Component>
     */
    public function compile(array $definitions, bool $live = false, ?string $testIdPrefix = null): array
    {
        $grouped = [];

        foreach ($definitions as $definition) {
            $grouped[$definition->getGroup()][] = $definition;
        }

        $components = [];

        foreach ($grouped as $group => $groupDefinitions) {
            $components[] = Section::make($group)
                ->schema($this->compileFields($groupDefinitions, $live, $testIdPrefix))
                ->columns($this->resolveColumns($groupDefinitions));
        }

        return $components;
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     * @return array<int, Component>
     */
    private function compileFields(array $definitions, bool $live = false, ?string $testIdPrefix = null): array
    {
        return array_map(
            fn (KnobDefinition $definition): Component => $this->compileField($definition, $live, $testIdPrefix),
            $definitions,
        );
    }

    private function compileField(KnobDefinition $definition, bool $live = false, ?string $testIdPrefix = null): Component
    {
        $component = match ($definition->getType()) {
            KnobDefinition::TYPE_BOOLEAN => Toggle::make($definition->getName())
                ->inline(false),
            KnobDefinition::TYPE_SELECT => Select::make($definition->getName())
                ->options($definition->getOptions()),
            KnobDefinition::TYPE_NUMBER => TextInput::make($definition->getName())
                ->numeric()
                ->inputMode('numeric'),
            KnobDefinition::TYPE_FILE => $this->makeFileUpload($definition),
            KnobDefinition::TYPE_REPEATER => $this->makeRepeater($definition, $live, $testIdPrefix),
            default => TextInput::make($definition->getName()),
        };

        if ($component instanceof Field) {
            $component
                ->label($definition->getLabel())
                ->helperText($definition->getHelperText());

            if ($definition->isRequired()) {
                $component->required();
            }

            if ($definition->getDefault() !== null) {
                $component->default($definition->getDefault());
            }

            if ($live) {
                $this->applyLiveBinding($component, $definition);
            }

            $this->applyTestingAttributes($component, $definition, $testIdPrefix);
        }

        if (
            in_array($definition->getType(), [KnobDefinition::TYPE_FILE, KnobDefinition::TYPE_REPEATER], true) &&
            method_exists($component, 'columnSpanFull')
        ) {
            $component->columnSpanFull();
        }

        return $component;
    }

    private function makeFileUpload(KnobDefinition $definition): NativeFileUpload
    {
        $component = NativeFileUpload::make($definition->getName())
            ->disk($definition->getFileDisk() ?? 'public');

        if ($definition->isImageFile()) {
            $component->image();
        }

        return $component;
    }

    private function makeRepeater(KnobDefinition $definition, bool $live = false, ?string $testIdPrefix = null): Repeater
    {
        $component = Repeater::make($definition->getName())
            ->schema($this->compileFields(
                $definition->getRepeaterSchema(),
                $live,
                $testIdPrefix ? "{$testIdPrefix}-{$definition->getName()}-item" : null,
            ));

        if ($definition->getDefault() !== null) {
            $component->default($definition->getDefault());
        }

        if ($definition->getRepeaterAddActionLabel()) {
            $component->addActionLabel($definition->getRepeaterAddActionLabel());
        }

        if ($definition->getMinItems() !== null) {
            $component->minItems($definition->getMinItems());
        }

        if ($definition->getMaxItems() !== null) {
            $component->maxItems($definition->getMaxItems());
        }

        if ($itemLabelField = $definition->getRepeaterItemLabelField()) {
            $component->itemLabel(static function (array $state) use ($itemLabelField): ?string {
                $label = $state[$itemLabelField] ?? null;

                if (! is_string($label)) {
                    return null;
                }

                $label = trim($label);

                return $label !== '' ? $label : null;
            });
        }

        return $component;
    }

    private function applyLiveBinding(Field $component, KnobDefinition $definition): void
    {
        if ($definition->getType() === KnobDefinition::TYPE_FILE) {
            return;
        }

        if (in_array($definition->getType(), [KnobDefinition::TYPE_TEXT, KnobDefinition::TYPE_NUMBER], true)) {
            $component->live(debounce: 150);

            return;
        }

        $component->live();
    }

    private function applyTestingAttributes(Field $component, KnobDefinition $definition, ?string $testIdPrefix = null): void
    {
        if ($testIdPrefix === null) {
            return;
        }

        $baseTestId = "{$testIdPrefix}-{$definition->getName()}";

        if (method_exists($component, 'extraFieldWrapperAttributes')) {
            $component->extraFieldWrapperAttributes([
                'data-testid' => $baseTestId,
            ]);
        }

        if (method_exists($component, 'extraInputAttributes')) {
            $component->extraInputAttributes([
                'data-testid' => "{$baseTestId}-{$this->resolveInputSuffix($definition)}",
            ]);
        }

        if (method_exists($component, 'inputAttributes')) {
            $component->inputAttributes([
                'data-testid' => "{$baseTestId}-{$this->resolveInputSuffix($definition)}",
            ]);
        }
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     */
    private function resolveColumns(array $definitions): int
    {
        if (count($definitions) === 1) {
            return 1;
        }

        foreach ($definitions as $definition) {
            if (in_array($definition->getType(), [KnobDefinition::TYPE_FILE, KnobDefinition::TYPE_REPEATER], true)) {
                return 1;
            }
        }

        return 2;
    }

    private function resolveInputSuffix(KnobDefinition $definition): string
    {
        return match ($definition->getType()) {
            KnobDefinition::TYPE_BOOLEAN => 'toggle',
            KnobDefinition::TYPE_SELECT => 'select',
            KnobDefinition::TYPE_FILE => 'file',
            KnobDefinition::TYPE_REPEATER => 'repeater',
            default => 'input',
        };
    }
}

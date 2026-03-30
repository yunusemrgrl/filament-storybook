<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\StarterKits\StrukturaEngine\Compilers\ComputedNodeCompiler;
use App\Support\Engine\Ast\EngineNode;
use App\Support\Engine\Compiler\Contracts\CompilesEngineNode;
use App\Support\Engine\NodeRuleMatrix;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\View;

class FormNodeCompiler implements CompilesEngineNode
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
        private readonly ComputedNodeCompiler $computedNodeCompiler,
    ) {}

    public function supports(EngineNode $node): bool
    {
        return in_array(
            $this->nodeRuleMatrix->familyForType($node->type),
            [NodeRuleMatrix::FAMILY_FORM, NodeRuleMatrix::FAMILY_REPEATER],
            true,
        );
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    public function compile(EngineNode $node, array $compiledChildren, CompileContext $context): CompiledNode
    {
        $canonicalType = $this->nodeRuleMatrix->canonicalType($node->type);
        $statePath = $this->resolveStatePath($node);
        $artifact = match ($canonicalType) {
            'filament.form.text_input' => $this->compileTextInput($node, $statePath),
            'filament.form.money' => $this->compileMoney($node, $statePath),
            'filament.form.date_time' => $this->compileDateTime($node, $statePath),
            'filament.form.select' => $this->compileSelect($node, $statePath),
            'filament.form.file_upload' => $this->compileFileUpload($node, $statePath),
            'filament.form.repeater' => $this->compileRepeater($node, $statePath, $compiledChildren, $context),
            default => null,
        };

        return new CompiledNode(
            node: $node,
            family: $this->nodeRuleMatrix->familyForType($node->type),
            compiler: static::class,
            artifact: $artifact,
            runtimeClass: is_object($artifact) ? $artifact::class : null,
            summary: [
                'payloadPath' => is_string($node->props['payload_path'] ?? null) ? $node->props['payload_path'] : $statePath,
                'statePath' => $statePath,
                'relationship' => is_string($node->props['relationship'] ?? null) ? $node->props['relationship'] : null,
                'dataSourceModel' => is_string($node->props['data_source_model'] ?? null) ? $node->props['data_source_model'] : null,
                'hydrationLogic' => is_string($node->props['hydration_logic'] ?? null) ? $node->props['hydration_logic'] : null,
                'validationRules' => is_string($node->props['validation_rules'] ?? null) ? $node->props['validation_rules'] : null,
                'computedExpression' => ComputedNodeCompiler::expression($node->computedLogic()),
                'currency' => is_string($node->props['currency'] ?? null) ? $node->props['currency'] : null,
                'locale' => is_string($node->props['locale'] ?? null) ? $node->props['locale'] : null,
                'format' => is_string($node->props['format'] ?? null) ? $node->props['format'] : null,
                'timezone' => is_string($node->props['timezone'] ?? null) ? $node->props['timezone'] : null,
                'ownerModel' => $context->ownerModel,
                'ownerRelationship' => $context->ownerRelationship,
                'ownerRelationshipType' => $context->ownerRelationshipType,
                'children' => count($compiledChildren),
                'mode' => $context->mode,
            ],
            children: $compiledChildren,
        );
    }

    private function compileTextInput(EngineNode $node, string $statePath): TextInput
    {
        $component = TextInput::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->required((bool) ($node->props['is_required'] ?? false));

        if (is_string($node->props['placeholder'] ?? null) && trim((string) $node->props['placeholder']) !== '') {
            $component->placeholder((string) $node->props['placeholder']);
        }

        if (is_string($node->props['helper_text'] ?? null) && trim((string) $node->props['helper_text']) !== '') {
            $component->helperText((string) $node->props['helper_text']);
        }

        if (($node->props['min_length'] ?? null) !== null) {
            $component->minLength((int) $node->props['min_length']);
        }

        if (($node->props['max_length'] ?? null) !== null) {
            $component->maxLength((int) $node->props['max_length']);
        }

        $inputMode = $node->props['input_mode'] ?? null;

        if (is_string($inputMode) && trim($inputMode) !== '') {
            $component->inputMode($inputMode);
        }

        return $component;
    }

    private function compileMoney(EngineNode $node, string $statePath): TextInput
    {
        $component = TextInput::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->required((bool) ($node->props['is_required'] ?? false))
            ->inputMode('decimal')
            ->dehydrateStateUsing(
                static fn (mixed $state): ?int => ComputedNodeCompiler::dehydrateMoneyInput($state, $node->props),
            )
            ->formatStateUsing(
                static fn (mixed $state): string => ComputedNodeCompiler::hydrateMoneyInput($state, $node->props),
            );

        if (is_string($node->props['prefix'] ?? null) && trim((string) $node->props['prefix']) !== '') {
            $component->prefix((string) $node->props['prefix']);
        }

        if (is_string($node->props['placeholder'] ?? null) && trim((string) $node->props['placeholder']) !== '') {
            $component->placeholder((string) $node->props['placeholder']);
        }

        if (is_string($node->props['helper_text'] ?? null) && trim((string) $node->props['helper_text']) !== '') {
            $component->helperText((string) $node->props['helper_text']);
        }

        return $component;
    }

    private function compileDateTime(EngineNode $node, string $statePath): DateTimePicker
    {
        $component = DateTimePicker::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->required((bool) ($node->props['is_required'] ?? false));

        if (is_string($node->props['helper_text'] ?? null) && trim((string) $node->props['helper_text']) !== '') {
            $component->helperText((string) $node->props['helper_text']);
        }

        if (is_string($node->props['format'] ?? null) && trim((string) $node->props['format']) !== '') {
            $component->displayFormat((string) $node->props['format']);
        }

        if ((bool) ($node->props['seconds'] ?? false)) {
            $component->seconds();
        }

        if (is_string($node->props['timezone'] ?? null) && trim((string) $node->props['timezone']) !== '') {
            $component->timezone((string) $node->props['timezone']);
        }

        if (is_string($node->props['min_date'] ?? null) && trim((string) $node->props['min_date']) !== '') {
            $component->minDate((string) $node->props['min_date']);
        }

        if (is_string($node->props['max_date'] ?? null) && trim((string) $node->props['max_date']) !== '') {
            $component->maxDate((string) $node->props['max_date']);
        }

        return $component;
    }

    private function compileSelect(EngineNode $node, string $statePath): Select
    {
        $component = Select::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->required((bool) ($node->props['is_required'] ?? false))
            ->searchable((bool) ($node->props['is_searchable'] ?? false))
            ->multiple((bool) ($node->props['is_multiple'] ?? false));

        $options = $node->props['options'] ?? [];

        if (is_array($options)) {
            $component->options(collect($options)
                ->filter(static fn (mixed $option): bool => is_array($option))
                ->mapWithKeys(static fn (array $option): array => [
                    (string) ($option['value'] ?? '') => (string) ($option['label'] ?? ''),
                ])
                ->filter(static fn (string $label, string $value): bool => trim($value) !== '' && trim($label) !== '')
                ->all());
        }

        if (
            is_string($node->props['relationship'] ?? null) &&
            trim((string) $node->props['relationship']) !== '' &&
            is_string($node->props['display_column'] ?? null) &&
            trim((string) $node->props['display_column']) !== ''
        ) {
            $component->relationship(
                (string) $node->props['relationship'],
                (string) $node->props['display_column'],
            );
        }

        return $component;
    }

    private function compileFileUpload(EngineNode $node, string $statePath): FileUpload
    {
        $component = FileUpload::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->required((bool) ($node->props['is_required'] ?? false))
            ->disk((string) ($node->props['disk'] ?? 'public'))
            ->directory((string) ($node->props['directory'] ?? 'page-builder/uploads'))
            ->multiple((bool) ($node->props['is_multiple'] ?? false));

        if ((bool) ($node->props['is_image'] ?? false)) {
            $component->image();
        }

        return $component;
    }

    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     */
    private function compileRepeater(EngineNode $node, string $statePath, array $compiledChildren, CompileContext $context): Repeater
    {
        $computedChildren = $this->computedNodeCompiler->apply($compiledChildren);

        $component = Repeater::make($statePath)
            ->label((string) ($node->props['label'] ?? $node->label))
            ->schema(array_values(array_map(
                fn (CompiledNode $child): SchemaComponent|Field => $this->toSchemaComponent($child),
                $computedChildren,
            )));

        if (
            $context->mode === 'builder' &&
            is_string($node->props['relationship'] ?? null) &&
            trim((string) $node->props['relationship']) !== ''
        ) {
            $component->relationship((string) $node->props['relationship']);
        }

        if (is_string($node->props['item_label_path'] ?? null) && trim((string) $node->props['item_label_path']) !== '') {
            $itemLabelPath = (string) $node->props['item_label_path'];
            $component->itemLabel(static fn (array $state): ?string => isset($state[$itemLabelPath]) && is_scalar($state[$itemLabelPath])
                ? (string) $state[$itemLabelPath]
                : null);
        }

        return $component;
    }

    private function toSchemaComponent(CompiledNode $compiledNode): SchemaComponent|Field
    {
        if ($compiledNode->artifact instanceof SchemaComponent || $compiledNode->artifact instanceof Field) {
            return $compiledNode->artifact;
        }

        return View::make('filament.schemas.components.struktura-runtime-node')
            ->viewData([
                'compiledNode' => $compiledNode,
            ]);
    }

    private function resolveStatePath(EngineNode $node): string
    {
        foreach (['payload_path', 'column_path', 'widget_key'] as $candidate) {
            $value = $node->props[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return str($node->slug())
            ->replace('.', '_')
            ->value();
    }
}

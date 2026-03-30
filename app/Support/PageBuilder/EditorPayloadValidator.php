<?php

namespace App\Support\PageBuilder;

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\StarterKits\StrukturaEngine\Compilers\ComputedNodeCompiler;
use App\StarterKits\StrukturaEngine\Services\ModelIntrospector;
use App\StarterKits\StrukturaEngine\Services\ModelRegistry;
use App\StarterKits\StrukturaEngine\Services\NodeRuleMatrix;
use Illuminate\Support\Arr;

class EditorPayloadValidator
{
    public function __construct(
        private readonly NodeRuleMatrix $nodeRuleMatrix,
        private readonly ModelRegistry $modelRegistry,
        private readonly ModelIntrospector $modelIntrospector,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, array<int, string>>
     */
    public function errorsFor(ComponentSurface|string $surface, array $nodes): array
    {
        $schemas = collect(BlockRegistry::schemasForSurface($surface))
            ->keyBy('type');

        $errors = [];

        $this->validateNodes($schemas->all(), $nodes, 'nodes', $errors, null);

        return $errors;
    }

    /**
     * @param  array<string, array<string, mixed>>  $schemas
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateNodes(array $schemas, array $nodes, string $prefix, array &$errors, ?string $parentType): void
    {
        $availableStatePaths = collect($nodes)
            ->filter(static fn (mixed $node): bool => is_array($node))
            ->map(static function (array $node): ?string {
                $props = $node['props'] ?? ($node['data'] ?? null);

                if (! is_array($props)) {
                    return null;
                }

                foreach (['payload_path', 'column_path', 'widget_key'] as $candidate) {
                    $value = $props[$candidate] ?? null;

                    if (is_string($value) && trim($value) !== '') {
                        return trim($value);
                    }
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();

        foreach ($nodes as $index => $node) {
            if (! is_array($node)) {
                $this->pushError($errors, "{$prefix}.{$index}", 'Each schema node must be an object payload.');

                continue;
            }

            $type = $node['type'] ?? null;

            if (! is_string($type) || $type === '') {
                $this->pushError($errors, "{$prefix}.{$index}.type", 'Each schema node must declare a registered type.');

                continue;
            }

            if (! $this->nodeRuleMatrix->supportsChildType($parentType, $type)) {
                $this->pushError(
                    $errors,
                    "{$prefix}.{$index}.type",
                    $parentType === null
                        ? 'This schema node cannot exist at the root of the composition tree.'
                        : 'This schema node is not allowed inside the selected parent node.',
                );
            }

            $schema = $schemas[$type] ?? null;

            if (! is_array($schema)) {
                $this->pushError($errors, "{$prefix}.{$index}.type", 'The selected schema node is not registered for this surface.');

                continue;
            }

            $props = $node['props'] ?? ($node['data'] ?? null);

            if (! is_array($props)) {
                $this->pushError($errors, "{$prefix}.{$index}.props", 'Each schema node must contain a props object.');

                continue;
            }

            /** @var array<int, array<string, mixed>> $fields */
            $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];
            $this->validateFields($fields, $props, "{$prefix}.{$index}.props", $errors);
            $this->validateComputedLogic($node, $availableStatePaths, "{$prefix}.{$index}", $errors);

            $children = $node['children'] ?? [];

            if (! is_array($children)) {
                $this->pushError($errors, "{$prefix}.{$index}.children", 'Children must be an array of schema nodes.');

                continue;
            }

            if ($children !== [] && ! $this->nodeRuleMatrix->acceptsChildren($type)) {
                $this->pushError($errors, "{$prefix}.{$index}.children", 'This schema node does not accept nested child nodes.');

                continue;
            }

            $this->validateNodes($schemas, $children, "{$prefix}.{$index}.children", $errors, $type);
        }
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<int, string>  $availableStatePaths
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateComputedLogic(array $node, array $availableStatePaths, string $prefix, array &$errors): void
    {
        $computedLogic = $node['computed_logic'] ?? null;

        if (! is_array($computedLogic) || $computedLogic === []) {
            return;
        }

        $expression = ComputedNodeCompiler::expression($computedLogic);

        if ($expression === null) {
            $this->pushError($errors, "{$prefix}.computed_logic", 'Computed logic requires a non-empty expression.');

            return;
        }

        $dependencies = ComputedNodeCompiler::dependencies($expression);

        foreach ($dependencies as $dependency) {
            if (! in_array($dependency, $availableStatePaths, true)) {
                $this->pushError(
                    $errors,
                    "{$prefix}.computed_logic.expression",
                    'The computed expression references a field that does not exist in the current form scope.',
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $data
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateFields(array $fields, array $data, string $prefix, array &$errors): void
    {
        $allowedFieldNames = collect($fields)
            ->map(static fn (array $field): ?string => is_string($field['name'] ?? null) ? $field['name'] : null)
            ->filter()
            ->values()
            ->all();

        foreach (array_diff(array_keys($data), $allowedFieldNames) as $unexpectedField) {
            $this->pushError(
                $errors,
                "{$prefix}.{$unexpectedField}",
                'This payload key is not declared in the selected technical schema.',
            );
        }

        foreach ($fields as $field) {
            $fieldName = $field['name'] ?? null;

            if (! is_string($fieldName) || $fieldName === '') {
                continue;
            }

            $fieldPath = "{$prefix}.{$fieldName}";
            $value = $data[$fieldName] ?? null;
            $isPresent = array_key_exists($fieldName, $data);
            $isRequired = (bool) ($field['required'] ?? false);

            if ($isRequired && ! $this->hasRequiredValue($field, $value, $isPresent)) {
                $this->pushError($errors, $fieldPath, 'This field is required by the selected technical schema.');

                continue;
            }

            if (! $isPresent || $value === null || $value === '') {
                continue;
            }

            $type = $field['type'] ?? 'text';

            match ($type) {
                'text' => $this->validateTextField($fieldPath, $value, $errors),
                'number' => $this->validateNumberField($fieldPath, $value, $errors),
                'boolean' => $this->validateBooleanField($fieldPath, $value, $errors),
                'select' => $this->validateSelectField($field, $fieldPath, $value, $errors),
                'file' => $this->validateFileField($fieldPath, $value, $errors),
                'repeater' => $this->validateRepeaterField($field, $fieldPath, $value, $errors),
                default => $this->pushError($errors, $fieldPath, 'Unsupported field type in technical schema.'),
            };
        }

        $this->validateDataBindingPayload($data, $prefix, $errors);
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function hasRequiredValue(array $field, mixed $value, bool $isPresent): bool
    {
        if (! $isPresent) {
            return false;
        }

        return match ($field['type'] ?? 'text') {
            'boolean' => is_bool($value),
            'number' => is_numeric($value),
            'file' => $this->extractFilePath($value) !== null,
            'repeater' => is_array($value) && $value !== [],
            default => is_string($value) && trim($value) !== '',
        };
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateTextField(string $fieldPath, mixed $value, array &$errors): void
    {
        if (! is_string($value)) {
            $this->pushError($errors, $fieldPath, 'This field must be a string value.');
        }
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateNumberField(string $fieldPath, mixed $value, array &$errors): void
    {
        if (! is_numeric($value)) {
            $this->pushError($errors, $fieldPath, 'This field must be numeric.');
        }
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateBooleanField(string $fieldPath, mixed $value, array &$errors): void
    {
        if (! is_bool($value)) {
            $this->pushError($errors, $fieldPath, 'This field must be a boolean flag.');
        }
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateSelectField(array $field, string $fieldPath, mixed $value, array &$errors): void
    {
        if (! is_string($value)) {
            $this->pushError($errors, $fieldPath, 'This field must be a string option value.');

            return;
        }

        $allowedValues = collect($field['options'] ?? [])
            ->map(static fn (mixed $option): ?string => is_array($option) && is_string($option['value'] ?? null) ? $option['value'] : null)
            ->filter()
            ->values()
            ->all();

        if ($value !== '' && ! in_array($value, $allowedValues, true)) {
            $this->pushError($errors, $fieldPath, 'This field contains an option not declared in the technical schema.');
        }
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateFileField(string $fieldPath, mixed $value, array &$errors): void
    {
        if ($this->extractFilePath($value) === null) {
            $this->pushError($errors, $fieldPath, 'This field must contain a persisted file path.');
        }
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateRepeaterField(array $field, string $fieldPath, mixed $value, array &$errors): void
    {
        if (! is_array($value)) {
            $this->pushError($errors, $fieldPath, 'This field must be an array of schema rows.');

            return;
        }

        /** @var array<int, array<string, mixed>> $nestedFields */
        $nestedFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];

        foreach ($value as $index => $row) {
            if (! is_array($row)) {
                $this->pushError($errors, "{$fieldPath}.{$index}", 'Each repeater row must be an object payload.');

                continue;
            }

            $this->validateFields($nestedFields, $row, "{$fieldPath}.{$index}", $errors);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, array<int, string>>  $errors
     */
    private function validateDataBindingPayload(array $data, string $prefix, array &$errors): void
    {
        $modelClass = is_string($data['data_source_model'] ?? null) ? trim((string) $data['data_source_model']) : '';

        if ($modelClass === '') {
            return;
        }

        if (! $this->modelRegistry->isAllowed($modelClass)) {
            $this->pushError($errors, "{$prefix}.data_source_model", 'This model is not whitelisted for the engine registry.');

            return;
        }

        $relationship = is_string($data['relationship'] ?? null) ? trim((string) $data['relationship']) : '';
        $relationshipDescriptor = null;

        if ($relationship !== '') {
            $relationshipDescriptor = $this->modelIntrospector->relationshipDescriptorFor($modelClass, $relationship);

            if ($relationshipDescriptor === null) {
                $this->pushError($errors, "{$prefix}.relationship", 'This relationship does not exist on the selected model.');
            }
        }

        $relationshipType = is_string($data['relationship_type'] ?? null) ? trim((string) $data['relationship_type']) : '';

        if ($relationshipType !== '' && $relationshipDescriptor !== null && $relationshipType !== $relationshipDescriptor->type) {
            $this->pushError(
                $errors,
                "{$prefix}.relationship_type",
                'The selected relationship type does not match the Eloquent relationship metadata.',
            );
        }

        $columnSourceModel = $this->modelIntrospector->columnSourceModel(
            $modelClass,
            $relationshipDescriptor?->name,
        );

        foreach (['display_column', 'value_column'] as $fieldName) {
            $column = is_string($data[$fieldName] ?? null) ? trim((string) $data[$fieldName]) : '';

            if ($column === '') {
                continue;
            }

            if (! $this->modelIntrospector->hasColumn($columnSourceModel, $column)) {
                $this->pushError(
                    $errors,
                    "{$prefix}.{$fieldName}",
                    'The selected column does not exist on the bound Eloquent model.',
                );
            }
        }

        $columnPath = is_string($data['column_path'] ?? null) ? trim((string) $data['column_path']) : '';

        if ($columnPath !== '' && ! $this->modelIntrospector->supportsColumnPath($modelClass, $columnPath)) {
            $this->pushError(
                $errors,
                "{$prefix}.column_path",
                'The selected column path does not match the bound model metadata.',
            );
        }
    }

    private function extractFilePath(mixed $value): ?string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (! is_array($value)) {
            return null;
        }

        $path = Arr::get($value, 'path');

        return is_string($path) && trim($path) !== '' ? trim($path) : null;
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function pushError(array &$errors, string $fieldPath, string $message): void
    {
        $errors[$fieldPath] ??= [];
        $errors[$fieldPath][] = $message;
    }
}

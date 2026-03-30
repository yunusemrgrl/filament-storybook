<?php

namespace App\Support\Engine;

use App\Support\Engine\Descriptors\ColumnDescriptor;
use App\Support\Engine\Descriptors\ModelDescriptor;
use App\Support\Engine\Descriptors\RelationshipDescriptor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Str;
use ReflectionMethod;

final class ModelIntrospector
{
    /**
     * @var array<class-string, ModelDescriptor>
     */
    private array $descriptors = [];

    public function __construct(
        private readonly ModelRegistry $modelRegistry,
    ) {}

    /**
     * @return array<int, ModelDescriptor>
     */
    public function describeAllowedModels(): array
    {
        return array_map(
            fn (string $modelClass): ModelDescriptor => $this->describe($modelClass),
            $this->modelRegistry->modelClasses(),
        );
    }

    public function describe(string $modelClass): ModelDescriptor
    {
        if (array_key_exists($modelClass, $this->descriptors)) {
            return $this->descriptors[$modelClass];
        }

        $metadata = $this->modelRegistry->metadataFor($modelClass);

        if ($metadata === null) {
            throw new \InvalidArgumentException("Model [{$modelClass}] is not whitelisted for the engine.");
        }

        $model = $this->instantiateModel($modelClass);

        return $this->descriptors[$modelClass] = new ModelDescriptor(
            class: $modelClass,
            label: $metadata['label'],
            table: $model->getTable(),
            surfaces: $metadata['surfaces'],
            defaultDisplayColumn: $metadata['defaultDisplayColumn'] ?? $this->guessDisplayColumn($modelClass),
            defaultValueColumn: $metadata['defaultValueColumn'] ?? $model->getKeyName(),
            columns: $this->columnsForModel($model),
            relationships: $this->relationshipsForModel($modelClass, $model),
        );
    }

    /**
     * @return array<int, ColumnDescriptor>
     */
    public function columnsFor(string $modelClass): array
    {
        return $this->describe($modelClass)->columns;
    }

    /**
     * @return array<int, RelationshipDescriptor>
     */
    public function relationshipsFor(string $modelClass): array
    {
        return $this->describe($modelClass)->relationships;
    }

    public function hasColumn(string $modelClass, string $column): bool
    {
        return collect($this->columnsFor($modelClass))
            ->contains(static fn (ColumnDescriptor $descriptor): bool => $descriptor->name === $column);
    }

    public function hasRelationship(string $modelClass, string $relationship): bool
    {
        return $this->relationshipDescriptorFor($modelClass, $relationship) !== null;
    }

    public function relationshipDescriptorFor(string $modelClass, string $relationship): ?RelationshipDescriptor
    {
        foreach ($this->relationshipsFor($modelClass) as $descriptor) {
            if ($descriptor->name === $relationship) {
                return $descriptor;
            }
        }

        return null;
    }

    public function supportsColumnPath(string $modelClass, string $columnPath): bool
    {
        $segments = array_values(array_filter(explode('.', $columnPath), static fn (string $segment): bool => $segment !== ''));

        if ($segments === []) {
            return false;
        }

        if (count($segments) === 1) {
            return $this->hasColumn($modelClass, $segments[0]);
        }

        if (count($segments) !== 2) {
            return false;
        }

        $relationship = $this->relationshipDescriptorFor($modelClass, $segments[0]);

        if ($relationship === null) {
            return false;
        }

        return $this->hasColumn($relationship->relatedModel, $segments[1]);
    }

    public function columnSourceModel(string $modelClass, ?string $relationship): string
    {
        if (! is_string($relationship) || trim($relationship) === '') {
            return $modelClass;
        }

        return $this->relationshipDescriptorFor($modelClass, trim($relationship))?->relatedModel ?? $modelClass;
    }

    private function instantiateModel(string $modelClass): Model
    {
        $model = new $modelClass;

        if (! $model instanceof Model) {
            throw new \InvalidArgumentException("Configured engine model [{$modelClass}] is not an Eloquent model.");
        }

        return $model;
    }

    /**
     * @return array<int, ColumnDescriptor>
     */
    private function columnsForModel(Model $model): array
    {
        $schemaBuilder = $model->getConnection()->getSchemaBuilder();
        $columns = [];
        $casts = $model->getCasts();
        $metadataByName = [];

        if (method_exists($schemaBuilder, 'getColumns')) {
            foreach ($schemaBuilder->getColumns($model->getTable()) as $column) {
                if (! is_array($column) || ! is_string($column['name'] ?? null)) {
                    continue;
                }

                $metadataByName[$column['name']] = $column;
            }
        }

        foreach ($schemaBuilder->getColumnListing($model->getTable()) as $columnName) {
            $metadata = $metadataByName[$columnName] ?? [];

            $columns[] = new ColumnDescriptor(
                name: $columnName,
                label: Str::headline($columnName),
                databaseType: $this->resolveColumnType($schemaBuilder, $model, $columnName, $metadata),
                cast: is_string($casts[$columnName] ?? null) ? $casts[$columnName] : null,
                nullable: (bool) ($metadata['nullable'] ?? true),
            );
        }

        return $columns;
    }

    /**
     * @return array<int, RelationshipDescriptor>
     */
    private function relationshipsForModel(string $modelClass, Model $model): array
    {
        $relationships = [];

        foreach (get_class_methods($modelClass) as $methodName) {
            $reflection = new ReflectionMethod($modelClass, $methodName);

            if ($this->shouldSkipRelationshipMethod($reflection)) {
                continue;
            }

            try {
                $relation = $reflection->invoke($model);
            } catch (\Throwable) {
                continue;
            }

            if (! $relation instanceof Relation) {
                continue;
            }

            $relatedModel = $relation->getRelated()::class;

            $relationships[] = new RelationshipDescriptor(
                name: $methodName,
                type: $this->relationType($relation),
                relatedModel: $relatedModel,
                relatedLabel: $this->modelRegistry->metadataFor($relatedModel)['label'] ?? Str::headline(class_basename($relatedModel)),
                defaultDisplayColumn: $this->modelRegistry->defaultDisplayColumnFor($relatedModel) ?? $this->guessDisplayColumn($relatedModel),
                defaultValueColumn: $this->modelRegistry->defaultValueColumnFor($relatedModel) ?? $relation->getRelated()->getKeyName(),
            );
        }

        return $relationships;
    }

    private function shouldSkipRelationshipMethod(ReflectionMethod $reflection): bool
    {
        if ($reflection->isStatic() || $reflection->getNumberOfRequiredParameters() > 0) {
            return true;
        }

        $methodName = $reflection->getName();

        if (
            str_starts_with($methodName, 'get') ||
            str_starts_with($methodName, 'set') ||
            str_starts_with($methodName, 'scope') ||
            str_starts_with($methodName, 'new') ||
            str_starts_with($methodName, 'boot')
        ) {
            return true;
        }

        return ! str_starts_with($reflection->getDeclaringClass()->getName(), 'App\\');
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function resolveColumnType(SchemaBuilder $schemaBuilder, Model $model, string $columnName, array $metadata): string
    {
        if (is_string($metadata['type_name'] ?? null) && trim($metadata['type_name']) !== '') {
            return trim($metadata['type_name']);
        }

        if (is_string($metadata['type'] ?? null) && trim($metadata['type']) !== '') {
            return trim($metadata['type']);
        }

        try {
            return (string) $schemaBuilder->getColumnType($model->getTable(), $columnName);
        } catch (\Throwable) {
            return 'string';
        }
    }

    private function relationType(Relation $relation): string
    {
        return lcfirst(class_basename($relation));
    }

    private function guessDisplayColumn(string $modelClass): ?string
    {
        $model = $this->instantiateModel($modelClass);
        $schemaBuilder = $model->getConnection()->getSchemaBuilder();
        $columns = $schemaBuilder->getColumnListing($model->getTable());

        foreach (['name', 'title', 'label', 'email', 'slug'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return $columns[0] ?? null;
    }
}

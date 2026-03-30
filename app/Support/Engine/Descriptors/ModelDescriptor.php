<?php

namespace App\Support\Engine\Descriptors;

final readonly class ModelDescriptor
{
    /**
     * @param  array<int, string>  $surfaces
     * @param  array<int, ColumnDescriptor>  $columns
     * @param  array<int, RelationshipDescriptor>  $relationships
     */
    public function __construct(
        public string $class,
        public string $label,
        public string $table,
        public array $surfaces,
        public ?string $defaultDisplayColumn,
        public ?string $defaultValueColumn,
        public array $columns,
        public array $relationships,
    ) {}

    /**
     * @return array{
     *     class: string,
     *     label: string,
     *     table: string,
     *     surfaces: array<int, string>,
     *     defaultDisplayColumn: string|null,
     *     defaultValueColumn: string|null,
     *     columns: array<int, array{name: string, label: string, databaseType: string, cast: string|null, nullable: bool}>,
     *     relationships: array<int, array{name: string, type: string, relatedModel: string, relatedLabel: string, defaultDisplayColumn: string|null, defaultValueColumn: string|null}>
     * }
     */
    public function toArray(): array
    {
        return [
            'class' => $this->class,
            'label' => $this->label,
            'table' => $this->table,
            'surfaces' => $this->surfaces,
            'defaultDisplayColumn' => $this->defaultDisplayColumn,
            'defaultValueColumn' => $this->defaultValueColumn,
            'columns' => array_map(
                static fn (ColumnDescriptor $column): array => $column->toArray(),
                $this->columns,
            ),
            'relationships' => array_map(
                static fn (RelationshipDescriptor $relationship): array => $relationship->toArray(),
                $this->relationships,
            ),
        ];
    }
}

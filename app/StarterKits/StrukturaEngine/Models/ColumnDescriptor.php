<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Models;

final readonly class ColumnDescriptor
{
    public function __construct(
        public string $name,
        public string $label,
        public string $databaseType,
        public ?string $cast,
        public bool $nullable,
    ) {}

    /**
     * @return array{name: string, label: string, databaseType: string, cast: string|null, nullable: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'databaseType' => $this->databaseType,
            'cast' => $this->cast,
            'nullable' => $this->nullable,
        ];
    }
}

<?php

namespace App\Support\Engine\Descriptors;

final readonly class RelationshipDescriptor
{
    public function __construct(
        public string $name,
        public string $type,
        public string $relatedModel,
        public string $relatedLabel,
        public ?string $defaultDisplayColumn,
        public ?string $defaultValueColumn,
    ) {}

    /**
     * @return array{
     *     name: string,
     *     type: string,
     *     relatedModel: string,
     *     relatedLabel: string,
     *     defaultDisplayColumn: string|null,
     *     defaultValueColumn: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'relatedModel' => $this->relatedModel,
            'relatedLabel' => $this->relatedLabel,
            'defaultDisplayColumn' => $this->defaultDisplayColumn,
            'defaultValueColumn' => $this->defaultValueColumn,
        ];
    }
}

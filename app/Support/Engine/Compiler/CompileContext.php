<?php

declare(strict_types=1);

namespace App\Support\Engine\Compiler;

use App\ComponentSurface;
use App\Support\Engine\Ast\EngineNode;
use Illuminate\Database\Eloquent\Model;

readonly class CompileContext
{
    public function __construct(
        public ComponentSurface $surface,
        public string $mode = 'runtime',
        public string $operation = 'render',
        public ?Model $record = null,
        public ?string $parentType = null,
        public ?string $ownerModel = null,
        public ?string $ownerRelationship = null,
        public ?string $ownerRelationshipType = null,
    ) {}

    public function forChildNode(EngineNode $node): self
    {
        $ownerModel = $this->ownerModel;
        $ownerRelationship = $this->ownerRelationship;
        $ownerRelationshipType = $this->ownerRelationshipType;

        if ($node->canonicalType() === 'filament.form.repeater') {
            $ownerModel = is_string($node->props['data_source_model'] ?? null) && trim((string) $node->props['data_source_model']) !== ''
                ? trim((string) $node->props['data_source_model'])
                : $ownerModel;
            $ownerRelationship = is_string($node->props['relationship'] ?? null) && trim((string) $node->props['relationship']) !== ''
                ? trim((string) $node->props['relationship'])
                : $ownerRelationship;
            $ownerRelationshipType = is_string($node->props['relationship_type'] ?? null) && trim((string) $node->props['relationship_type']) !== ''
                ? trim((string) $node->props['relationship_type'])
                : $ownerRelationshipType;
        }

        return new self(
            surface: $this->surface,
            mode: $this->mode,
            operation: $this->operation,
            record: $this->record,
            parentType: $node->type,
            ownerModel: $ownerModel,
            ownerRelationship: $ownerRelationship,
            ownerRelationshipType: $ownerRelationshipType,
        );
    }
}

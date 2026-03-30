<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Workflow;

use App\StarterKits\StrukturaEngine\Actions\ActionDefinition;
use App\Support\Engine\Ast\EngineNode;
use BackedEnum;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

readonly class WorkflowActionContext
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public ActionDefinition $definition,
        public EngineNode $actionNode,
        public Model $record,
        public ?Authenticatable $actor,
        public array $data = [],
        public string $operation = 'execute',
    ) {}

    public function actionName(): string
    {
        return $this->definition->name;
    }

    public function currentState(): ?string
    {
        $state = $this->record->getAttribute($this->definition->stateField);

        if ($state instanceof BackedEnum) {
            return is_string($state->value) && trim($state->value) !== ''
                ? trim($state->value)
                : null;
        }

        if ($state instanceof UnitEnum) {
            return trim($state->name) !== ''
                ? trim($state->name)
                : null;
        }

        return is_string($state) && trim($state) !== ''
            ? trim($state)
            : null;
    }
}

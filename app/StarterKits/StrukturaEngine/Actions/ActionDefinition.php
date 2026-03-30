<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use Illuminate\Database\Eloquent\Model;

readonly class ActionDefinition
{
    /**
     * @param  array<int, string>  $fromStates
     */
    public function __construct(
        public string $name,
        public string $modelClass,
        public string $handlerClass,
        public ?string $guardAlias,
        public ?string $guardClass,
        public string $event,
        public array $fromStates = [],
        public ?string $toState = null,
        public string $stateField = 'status',
    ) {}

    public function supports(Model|string $record): bool
    {
        $recordClass = $record instanceof Model ? $record::class : $record;

        return is_a($recordClass, $this->modelClass, true);
    }

    public function canTransitionFrom(?string $state): bool
    {
        if ($this->fromStates === []) {
            return true;
        }

        return in_array((string) $state, $this->fromStates, true);
    }
}

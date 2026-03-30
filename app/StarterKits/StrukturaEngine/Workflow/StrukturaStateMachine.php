<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Workflow;

use App\StarterKits\StrukturaEngine\Actions\ActionDefinition;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use UnitEnum;

class StrukturaStateMachine
{
    public function availabilityFor(ActionDefinition $definition, Model $record): GuardDecision
    {
        $currentState = $this->currentState($definition, $record);

        if ($currentState === 'archived') {
            return GuardDecision::disable('Archived invoices are locked and cannot transition.');
        }

        if ($definition->canTransitionFrom($currentState)) {
            return GuardDecision::allow();
        }

        $allowedStates = implode(', ', $definition->fromStates);

        return GuardDecision::disable("This action is only available when the record is in: {$allowedStates}.");
    }

    public function transition(ActionDefinition $definition, Model $record, ?string $targetState): void
    {
        if ($targetState === null || trim($targetState) === '') {
            return;
        }

        $availability = $this->availabilityFor($definition, $record);

        if (! $availability->enabled) {
            throw new LogicException($availability->reason ?? 'The requested workflow transition is not allowed.');
        }

        $stateField = $definition->stateField;

        $record->forceFill([
            $stateField => $targetState,
        ]);

        $timestampField = $this->timestampFieldFor($record::class, $targetState);

        if ($timestampField !== null && blank($record->getAttribute($timestampField))) {
            $record->forceFill([
                $timestampField => now(),
            ]);
        }

        $record->save();
    }

    public function colorFor(Model|string $record, mixed $state): ?string
    {
        $normalizedState = $this->normalizeState($state);

        if ($normalizedState === null) {
            return null;
        }

        $modelClass = $record instanceof Model ? $record::class : $record;
        $configuration = $this->modelConfiguration($modelClass);

        if (! is_array($configuration)) {
            return null;
        }

        $colors = $configuration['state_colors'] ?? null;

        if (! is_array($colors)) {
            return null;
        }

        $color = $colors[$normalizedState] ?? null;

        return is_string($color) && trim($color) !== ''
            ? trim($color)
            : null;
    }

    public function stateFieldFor(string $modelClass): string
    {
        $configuration = $this->modelConfiguration($modelClass);
        $stateField = is_array($configuration) ? ($configuration['state_field'] ?? null) : null;

        return is_string($stateField) && trim($stateField) !== ''
            ? trim($stateField)
            : 'status';
    }

    private function currentState(ActionDefinition $definition, Model $record): ?string
    {
        return $this->normalizeState(
            $record->getAttribute($definition->stateField),
        );
    }

    private function timestampFieldFor(string $modelClass, string $state): ?string
    {
        $configuration = $this->modelConfiguration($modelClass);
        $timestampFields = is_array($configuration) ? ($configuration['state_timestamps'] ?? null) : null;

        if (! is_array($timestampFields)) {
            return null;
        }

        $field = $timestampFields[$state] ?? null;

        return is_string($field) && trim($field) !== ''
            ? trim($field)
            : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function modelConfiguration(string $modelClass): ?array
    {
        $models = config('struktura-engine.workflow.models', []);

        if (! is_array($models)) {
            return null;
        }

        foreach ($models as $configuredModelClass => $configuration) {
            if (! is_string($configuredModelClass) || ! is_array($configuration)) {
                continue;
            }

            if (is_a($modelClass, $configuredModelClass, true)) {
                return $configuration;
            }
        }

        return null;
    }

    private function normalizeState(mixed $state): ?string
    {
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

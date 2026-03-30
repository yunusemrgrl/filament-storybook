<?php

namespace App\Support\Engine;

use App\ComponentSurface;
use Illuminate\Support\Str;

final class ModelRegistry
{
    /**
     * @return array<int, array{
     *     class: class-string,
     *     label: string,
     *     surfaces: array<int, string>,
     *     defaultDisplayColumn: string|null,
     *     defaultValueColumn: string|null
     * }>
     */
    public function models(?ComponentSurface $surface = null): array
    {
        return array_values(array_filter(
            array_map(
                fn (string $class, mixed $config): array => $this->normalizeModelConfig($class, is_array($config) ? $config : []),
                array_keys($this->configuredModels()),
                $this->configuredModels(),
            ),
            static fn (array $model): bool => $surface === null || in_array($surface->value, $model['surfaces'], true),
        ));
    }

    /**
     * @return array<int, class-string>
     */
    public function modelClasses(?ComponentSurface $surface = null): array
    {
        return array_map(
            static fn (array $model): string => $model['class'],
            $this->models($surface),
        );
    }

    public function isAllowed(string $modelClass, ?ComponentSurface $surface = null): bool
    {
        return collect($this->models($surface))
            ->contains(static fn (array $model): bool => $model['class'] === $modelClass);
    }

    /**
     * @return array{
     *     class: class-string,
     *     label: string,
     *     surfaces: array<int, string>,
     *     defaultDisplayColumn: string|null,
     *     defaultValueColumn: string|null
     * }|null
     */
    public function metadataFor(string $modelClass): ?array
    {
        foreach ($this->models() as $model) {
            if ($model['class'] === $modelClass) {
                return $model;
            }
        }

        return null;
    }

    public function defaultDisplayColumnFor(string $modelClass): ?string
    {
        return $this->metadataFor($modelClass)['defaultDisplayColumn'] ?? null;
    }

    public function defaultValueColumnFor(string $modelClass): ?string
    {
        return $this->metadataFor($modelClass)['defaultValueColumn'] ?? null;
    }

    /**
     * @return array<class-string, mixed>
     */
    private function configuredModels(): array
    {
        $models = config('struktura-engine.engine_models', []);

        return is_array($models) ? $models : [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{
     *     class: class-string,
     *     label: string,
     *     surfaces: array<int, string>,
     *     defaultDisplayColumn: string|null,
     *     defaultValueColumn: string|null
     * }
     */
    private function normalizeModelConfig(string $class, array $config): array
    {
        $surfaces = collect($config['surfaces'] ?? [])
            ->filter(static fn (mixed $surface): bool => is_string($surface) && $surface !== '')
            ->values()
            ->all();

        return [
            'class' => $class,
            'label' => is_string($config['label'] ?? null) && trim($config['label']) !== ''
                ? trim($config['label'])
                : Str::headline(class_basename($class)),
            'surfaces' => $surfaces,
            'defaultDisplayColumn' => is_string($config['default_display_column'] ?? null) && trim($config['default_display_column']) !== ''
                ? trim($config['default_display_column'])
                : null,
            'defaultValueColumn' => is_string($config['default_value_column'] ?? null) && trim($config['default_value_column']) !== ''
                ? trim($config['default_value_column'])
                : null,
        ];
    }
}

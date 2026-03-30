<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Workflow;

use Illuminate\Database\Eloquent\Builder;
use Throwable;

class QueryScopeApplier
{
    public function apply(Builder $query, ?string $queryScope): Builder
    {
        if (! is_string($queryScope) || trim($queryScope) === '') {
            return $query;
        }

        $segments = preg_split('/\s*->\s*/', trim($queryScope)) ?: [];

        foreach ($segments as $segment) {
            $segment = trim($segment);

            if ($segment === '') {
                continue;
            }

            if (! preg_match('/^(?<method>[A-Za-z_][A-Za-z0-9_]*)\((?<arguments>.*)\)$/', $segment, $matches)) {
                continue;
            }

            $method = $matches['method'] ?? null;

            if (! is_string($method) || $method === '' || str_starts_with($method, '__')) {
                continue;
            }

            $arguments = $this->parseArguments((string) ($matches['arguments'] ?? ''));

            try {
                $query = $query->{$method}(...$arguments);
            } catch (Throwable) {
                continue;
            }
        }

        return $query;
    }

    /**
     * @return array<int, mixed>
     */
    private function parseArguments(string $arguments): array
    {
        if (trim($arguments) === '') {
            return [];
        }

        return array_map(function (string $argument): mixed {
            $argument = trim($argument);

            if ($argument === 'null') {
                return null;
            }

            if ($argument === 'true') {
                return true;
            }

            if ($argument === 'false') {
                return false;
            }

            if (is_numeric($argument)) {
                return str_contains($argument, '.') ? (float) $argument : (int) $argument;
            }

            if (
                (str_starts_with($argument, '"') && str_ends_with($argument, '"')) ||
                (str_starts_with($argument, "'") && str_ends_with($argument, "'"))
            ) {
                return substr($argument, 1, -1);
            }

            return $argument;
        }, str_getcsv($arguments, ',', '"'));
    }
}

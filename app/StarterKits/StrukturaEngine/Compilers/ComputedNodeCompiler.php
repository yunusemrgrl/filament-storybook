<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Compilers;

use App\Support\Engine\Compiler\CompiledNode;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use NumberFormatter;

class ComputedNodeCompiler
{
    /**
     * @param  array<int, CompiledNode>  $compiledChildren
     * @return array<int, CompiledNode>
     */
    public function apply(array $compiledChildren): array
    {
        $fieldNodes = [];

        foreach ($compiledChildren as $compiledChild) {
            if (! $compiledChild->artifact instanceof Field) {
                continue;
            }

            $statePath = $this->statePathFor($compiledChild);

            if ($statePath === null) {
                continue;
            }

            $fieldNodes[$statePath] = $compiledChild;
        }

        foreach ($compiledChildren as $compiledChild) {
            if (! $compiledChild->artifact instanceof Field) {
                continue;
            }

            $computedLogic = $compiledChild->node->computedLogic();
            $expression = self::expression($computedLogic);

            if ($expression === null) {
                continue;
            }

            $targetStatePath = $this->statePathFor($compiledChild);

            if ($targetStatePath === null) {
                continue;
            }

            $dependencies = self::dependencies($expression);
            $dependencyMetadata = [];

            foreach ($dependencies as $dependency) {
                $sourceNode = $fieldNodes[$dependency] ?? null;

                if (! $sourceNode?->artifact instanceof Field) {
                    continue;
                }

                $dependencyMetadata[$dependency] = [
                    'type' => $sourceNode->node->canonicalType(),
                    'props' => $sourceNode->node->props,
                ];

                if (method_exists($sourceNode->artifact, 'live')) {
                    $sourceNode->artifact->live();
                }

                $sourceNode->artifact->afterStateUpdated(
                    static function (Get $get, Set $set) use ($expression, $dependencies, $dependencyMetadata, $targetStatePath, $compiledChild): void {
                        $result = self::evaluateExpression($expression, $dependencies, $get, $dependencyMetadata);

                        $set(
                            $targetStatePath,
                            self::formatTargetValue(
                                $result,
                                $compiledChild->node->canonicalType(),
                                $compiledChild->node->props,
                                $compiledChild->node->computedLogic(),
                            ),
                        );
                    },
                );
            }

            if (method_exists($compiledChild->artifact, 'readOnly')) {
                $compiledChild->artifact->readOnly();
            }

            $compiledChild->artifact->afterStateHydrated(
                static function (Get $get, Set $set) use ($expression, $dependencies, $dependencyMetadata, $targetStatePath, $compiledChild): void {
                    $result = self::evaluateExpression($expression, $dependencies, $get, $dependencyMetadata);

                    $set(
                        $targetStatePath,
                        self::formatTargetValue(
                            $result,
                            $compiledChild->node->canonicalType(),
                            $compiledChild->node->props,
                            $compiledChild->node->computedLogic(),
                        ),
                    );
                },
            );
        }

        return $compiledChildren;
    }

    /**
     * @param  array<string, mixed>  $computedLogic
     */
    public static function expression(array $computedLogic): ?string
    {
        $expression = $computedLogic['expression'] ?? null;

        if (! is_string($expression) || trim($expression) === '') {
            return null;
        }

        return trim($expression);
    }

    /**
     * @return array<int, string>
     */
    public static function dependencies(string $expression): array
    {
        preg_match_all('/\{([A-Za-z0-9_.-]+)\}/', $expression, $matches);

        return array_values(array_unique(array_filter($matches[1] ?? [], 'is_string')));
    }

    /**
     * @param  array<int, string>  $dependencies
     * @param  array<string, array{type: string, props: array<string, mixed>}>  $dependencyMetadata
     */
    public static function evaluateExpression(string $expression, array $dependencies, Get $get, array $dependencyMetadata = []): float
    {
        $values = [];

        foreach ($dependencies as $dependency) {
            $values[$dependency] = $get($dependency);
        }

        return self::evaluateFormula($expression, $values, $dependencyMetadata);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, array{type: string, props: array<string, mixed>}>  $dependencyMetadata
     */
    public static function evaluateFormula(string $expression, array $values, array $dependencyMetadata = []): float
    {
        $resolvedExpression = $expression;

        foreach (self::dependencies($expression) as $dependency) {
            $metadata = $dependencyMetadata[$dependency] ?? ['type' => 'filament.form.text_input', 'props' => []];
            $numericValue = self::normalizeDependencyValue($values[$dependency] ?? null, $metadata['type'], $metadata['props']);
            $resolvedExpression = str_replace('{'.$dependency.'}', (string) $numericValue, $resolvedExpression);
        }

        return self::evaluateMathExpression($resolvedExpression);
    }

    /**
     * @param  array<string, mixed>  $targetProps
     * @param  array<string, mixed>  $computedLogic
     */
    public static function formatTargetValue(float $result, string $targetType, array $targetProps, array $computedLogic = []): int|string|float
    {
        $precision = (int) ($computedLogic['precision'] ?? ($targetType === 'filament.form.money' ? (int) ($targetProps['decimals'] ?? 2) : 2));
        $rounded = round($result, $precision);

        if ($targetType === 'filament.form.money') {
            return self::formatMoneyInput($rounded, $targetProps);
        }

        if ($targetType === 'filament.form.text_input' && (($targetProps['input_mode'] ?? null) === 'numeric' || ($targetProps['input_mode'] ?? null) === 'decimal')) {
            return $precision === 0
                ? (int) $rounded
                : $rounded;
        }

        return $precision === 0 ? (int) $rounded : $rounded;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    public static function formatMoneyInput(float $value, array $props): string
    {
        $decimals = (int) ($props['decimals'] ?? 2);
        $locale = is_string($props['locale'] ?? null) && trim((string) $props['locale']) !== ''
            ? trim((string) $props['locale'])
            : 'en';

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::GROUPING_USED, 0);

            $formatted = $formatter->format($value);

            if (is_string($formatted)) {
                return $formatted;
            }
        }

        return number_format($value, $decimals, '.', '');
    }

    /**
     * @param  array<string, mixed>  $props
     */
    public static function dehydrateMoneyInput(mixed $state, array $props): ?int
    {
        if ($state === null || $state === '') {
            return null;
        }

        $decimals = (int) ($props['decimals'] ?? 2);
        $numeric = self::parseLocalizedNumber($state, $props);

        return (int) round($numeric * (10 ** $decimals));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    public static function hydrateMoneyInput(mixed $state, array $props): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        $decimals = (int) ($props['decimals'] ?? 2);

        if (! is_numeric($state)) {
            return '';
        }

        $value = ((float) $state) / (10 ** $decimals);

        return self::formatMoneyInput($value, $props);
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private static function normalizeDependencyValue(mixed $value, string $type, array $props): float
    {
        if ($type === 'filament.form.money') {
            return self::parseLocalizedNumber($value, $props);
        }

        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return self::parseLocalizedNumber($value, $props);
        }

        return 0.0;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private static function parseLocalizedNumber(mixed $value, array $props): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return 0.0;
        }

        $locale = is_string($props['locale'] ?? null) && trim((string) $props['locale']) !== ''
            ? trim((string) $props['locale'])
            : 'en';

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::GROUPING_USED, 0);
            $parsed = $formatter->parse($stringValue, NumberFormatter::TYPE_DOUBLE);

            if (is_float($parsed) || is_int($parsed)) {
                return (float) $parsed;
            }
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', $stringValue) ?? '';

        if ($normalized === '' || $normalized === '-' || $normalized === '.' || $normalized === ',') {
            return 0.0;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            $normalized = str_replace($thousandsSeparator, '', $normalized);
            $normalized = str_replace($decimalSeparator, '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    private static function evaluateMathExpression(string $expression): float
    {
        $expression = preg_replace('/\s+/', '', $expression) ?? '';

        if ($expression === '') {
            return 0.0;
        }

        preg_match_all('/\d+(?:\.\d+)?|[()+\-*\/]/', $expression, $matches);
        $tokens = $matches[0] ?? [];

        if (implode('', $tokens) !== $expression) {
            throw new InvalidArgumentException('The computed logic expression contains unsupported tokens.');
        }

        $index = 0;
        $result = self::parseExpression($tokens, $index);

        if ($index !== count($tokens)) {
            throw new InvalidArgumentException('The computed logic expression could not be fully evaluated.');
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private static function parseExpression(array $tokens, int &$index): float
    {
        $value = self::parseTerm($tokens, $index);

        while ($index < count($tokens)) {
            $token = $tokens[$index];

            if (! in_array($token, ['+', '-'], true)) {
                break;
            }

            $index++;
            $right = self::parseTerm($tokens, $index);
            $value = $token === '+' ? $value + $right : $value - $right;
        }

        return $value;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private static function parseTerm(array $tokens, int &$index): float
    {
        $value = self::parseFactor($tokens, $index);

        while ($index < count($tokens)) {
            $token = $tokens[$index];

            if (! in_array($token, ['*', '/'], true)) {
                break;
            }

            $index++;
            $right = self::parseFactor($tokens, $index);
            $value = $token === '*'
                ? $value * $right
                : ($right === 0.0 ? 0.0 : $value / $right);
        }

        return $value;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private static function parseFactor(array $tokens, int &$index): float
    {
        $token = $tokens[$index] ?? null;

        if ($token === null) {
            throw new InvalidArgumentException('Unexpected end of computed logic expression.');
        }

        if ($token === '+') {
            $index++;

            return self::parseFactor($tokens, $index);
        }

        if ($token === '-') {
            $index++;

            return -self::parseFactor($tokens, $index);
        }

        if ($token === '(') {
            $index++;
            $value = self::parseExpression($tokens, $index);

            if (($tokens[$index] ?? null) !== ')') {
                throw new InvalidArgumentException('Unclosed group in computed logic expression.');
            }

            $index++;

            return $value;
        }

        if (! is_numeric($token)) {
            throw new InvalidArgumentException('Expected a numeric value in computed logic expression.');
        }

        $index++;

        return (float) $token;
    }

    private function statePathFor(CompiledNode $compiledNode): ?string
    {
        $statePath = Arr::get($compiledNode->summary, 'statePath');

        if (! is_string($statePath) || trim($statePath) === '') {
            $statePath = Arr::get($compiledNode->summary, 'payloadPath');
        }

        return is_string($statePath) && trim($statePath) !== ''
            ? trim($statePath)
            : null;
    }
}

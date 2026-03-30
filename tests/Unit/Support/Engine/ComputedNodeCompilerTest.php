<?php

declare(strict_types=1);

use App\StarterKits\StrukturaEngine\Compilers\ComputedNodeCompiler;

it('extracts dependencies from a computed expression', function (): void {
    expect(ComputedNodeCompiler::dependencies('{quantity} * {unit_price_cents}'))
        ->toBe(['quantity', 'unit_price_cents']);
});

it('evaluates a computed expression with arithmetic precedence', function (): void {
    expect(ComputedNodeCompiler::evaluateFormula(
        '{quantity} * {unit_price_cents}',
        [
            'quantity' => 3,
            'unit_price_cents' => '125.50',
        ],
        [
            'quantity' => ['type' => 'filament.form.text_input', 'props' => []],
            'unit_price_cents' => [
                'type' => 'filament.form.money',
                'props' => ['locale' => 'en_US', 'decimals' => 2],
            ],
        ],
    ))->toBe(376.5);
});

it('hydrates and dehydrates money values using locale-aware decimal formatting', function (): void {
    expect(ComputedNodeCompiler::hydrateMoneyInput(125050, [
        'locale' => 'en_US',
        'decimals' => 2,
    ]))->toBe('1250.50')
        ->and(ComputedNodeCompiler::dehydrateMoneyInput('1250.50', [
            'locale' => 'en_US',
            'decimals' => 2,
        ]))->toBe(125050);
});

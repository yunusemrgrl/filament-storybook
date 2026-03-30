<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Contracts;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

interface CompilesNavigationAst
{
    /**
     * @param  array<int, array<string, mixed>>  $ast
     * @return array{groups: array<int, NavigationGroup>, items: array<int, NavigationItem>}
     */
    public function compile(array $ast): array;
}

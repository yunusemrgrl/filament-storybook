<?php

declare(strict_types=1);

use App\StarterKits\StrukturaEngine\Compilers\NavigationCompiler;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

it('compiles grouped and ungrouped navigation nodes into filament navigation objects', function (): void {
    $compiler = app(NavigationCompiler::class);

    $compiled = $compiler->compile([
        [
            'id' => 'runtime-group',
            'type' => 'dropdown',
            'label' => 'Runtime Pages',
            'group' => 'Runtime',
            'icon' => 'heroicon-o-rectangle-stack',
            'children' => [
                [
                    'id' => 'user-management',
                    'type' => 'link',
                    'label' => 'User Management',
                    'href' => '/admin/struktura/user-management',
                    'icon' => 'heroicon-o-users',
                ],
            ],
        ],
        [
            'id' => 'settings-link',
            'type' => 'link',
            'label' => 'Settings',
            'href' => '/admin/settings',
            'icon' => 'heroicon-o-cog-6-tooth',
        ],
    ]);

    expect($compiled['groups'])->toHaveCount(1)
        ->and($compiled['items'])->toHaveCount(1)
        ->and($compiled['groups'][0])->toBeInstanceOf(NavigationGroup::class)
        ->and($compiled['items'][0])->toBeInstanceOf(NavigationItem::class);
});

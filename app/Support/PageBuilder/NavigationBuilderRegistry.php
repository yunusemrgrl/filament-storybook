<?php

namespace App\Support\PageBuilder;

class NavigationBuilderRegistry
{
    /**
     * @return array<int, array{key: string, title: string, description: string}>
     */
    public function templates(): array
    {
        return [
            [
                'key' => 'link',
                'title' => 'Link',
                'description' => 'Direct panel route or external admin destination.',
            ],
            [
                'key' => 'dropdown',
                'title' => 'Dropdown',
                'description' => 'Parent runtime group with nested Filament pages.',
            ],
            [
                'key' => 'mega',
                'title' => 'Mega Menu',
                'description' => 'Large information cluster for complex admin navigation.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tree(): array
    {
        return [
            [
                'id' => 'nav-runtime',
                'type' => 'dropdown',
                'label' => 'Runtime Pages',
                'href' => null,
                'icon' => 'heroicon-o-rectangle-stack',
                'group' => 'Runtime',
                'target' => 'same-tab',
                'visibility' => 'always',
                'description' => 'Compiled Struktura pages injected into the panel sidebar.',
                'children' => [
                    [
                        'id' => 'nav-runtime-users',
                        'type' => 'link',
                        'label' => 'User Management',
                        'href' => '/admin/struktura/user-management',
                        'icon' => 'heroicon-o-users',
                        'group' => 'Runtime',
                        'target' => 'same-tab',
                        'visibility' => 'always',
                        'description' => 'Runtime management workspace.',
                    ],
                    [
                        'id' => 'nav-runtime-analytics',
                        'type' => 'link',
                        'label' => 'System Analytics',
                        'href' => '/admin/struktura/system-analytics',
                        'icon' => 'heroicon-o-chart-bar-square',
                        'group' => 'Runtime',
                        'target' => 'same-tab',
                        'visibility' => 'always',
                        'description' => 'Compiled analytics dashboard.',
                    ],
                ],
            ],
            [
                'id' => 'nav-settings',
                'type' => 'link',
                'label' => 'Settings',
                'href' => '/admin/settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'group' => 'System',
                'target' => 'same-tab',
                'visibility' => 'always',
                'description' => 'Engine-level configuration entry point.',
                'children' => [],
            ],
            [
                'id' => 'nav-mega-runtime',
                'type' => 'mega',
                'label' => 'Admin Clusters',
                'href' => '/admin',
                'icon' => 'heroicon-o-squares-plus',
                'group' => 'System',
                'target' => 'same-tab',
                'visibility' => 'always',
                'description' => 'Reserved cluster surface for future grouped admin modules.',
                'columns' => 3,
                'children' => [],
            ],
        ];
    }
}

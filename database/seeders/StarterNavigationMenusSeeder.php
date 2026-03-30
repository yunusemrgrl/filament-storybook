<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StarterNavigationMenusSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            [
                'id' => (string) Str::uuid(),
                'type' => 'dropdown',
                'label' => 'Runtime Pages',
                'href' => null,
                'icon' => 'heroicon-o-rectangle-stack',
                'group' => 'Runtime',
                'target' => 'same-tab',
                'visibility' => 'always',
                'description' => 'Published Struktura pages compiled into the Filament panel at runtime.',
                'children' => [
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'link',
                        'label' => 'User Management',
                        'href' => '/admin/struktura/user-management',
                        'icon' => 'heroicon-o-users',
                        'group' => 'Runtime',
                        'target' => 'same-tab',
                        'visibility' => 'always',
                        'description' => 'Compiled user management schema.',
                        'children' => [],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'link',
                        'label' => 'System Analytics',
                        'href' => '/admin/struktura/system-analytics',
                        'icon' => 'heroicon-o-chart-bar-square',
                        'group' => 'Runtime',
                        'target' => 'same-tab',
                        'visibility' => 'always',
                        'description' => 'Compiled analytics dashboard schema.',
                        'children' => [],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'link',
                        'label' => 'Manage Invoices',
                        'href' => '/admin/struktura/manage-invoices',
                        'icon' => 'heroicon-o-receipt-percent',
                        'group' => 'Runtime',
                        'target' => 'same-tab',
                        'visibility' => 'always',
                        'description' => 'Compiled SaaS invoice management workspace.',
                        'children' => [],
                    ],
                ],
            ],
        ];

        NavigationMenu::query()->updateOrCreate(
            ['key' => config('struktura-engine.navigation.menu_key', 'admin-sidebar')],
            [
                'name' => 'Admin Sidebar',
                'placement' => 'Sidebar',
                'channel' => 'Admin',
                'nodes' => $nodes,
                'draft_nodes' => $nodes,
                'is_active' => true,
            ],
        );
    }
}

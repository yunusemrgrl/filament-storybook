<?php

declare(strict_types=1);

use App\Models\NavigationMenu;
use App\Models\User;
use Database\Seeders\StarterNavigationMenusSeeder;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

it('injects compiled navigation items into the filament sidebar', function (): void {
    $this->seed(StarterNavigationMenusSeeder::class);

    $labels = collect(Filament::getCurrentPanel()->getNavigation())
        ->flatMap(fn (NavigationGroup $group) => flattenNavigationItems($group->getItems()))
        ->pluck('label');

    expect($labels)
        ->toContain('Runtime Pages')
        ->toContain('User Management')
        ->toContain('System Analytics')
        ->toContain('Manage Invoices');
});

it('persists navigation builder changes and surfaces them in the next panel refresh', function (): void {
    $user = User::factory()->create();

    $this->seed(StarterNavigationMenusSeeder::class);

    $this->actingAs($user)
        ->put(route('admin.navigation.builder.update'), [
            'name' => 'Admin Sidebar',
            'placement' => 'Sidebar',
            'channel' => 'Admin',
            'nodes' => [
                [
                    'id' => 'runtime-pages',
                    'type' => 'dropdown',
                    'label' => 'Runtime Pages',
                    'href' => null,
                    'icon' => 'heroicon-o-rectangle-stack',
                    'group' => 'Runtime',
                    'target' => 'same-tab',
                    'visibility' => 'always',
                    'description' => 'Published runtime pages.',
                    'children' => [
                        [
                            'id' => 'user-management',
                            'type' => 'link',
                            'label' => 'User Management',
                            'href' => '/admin/struktura/user-management',
                            'icon' => 'heroicon-o-users',
                            'group' => 'Runtime',
                            'target' => 'same-tab',
                            'visibility' => 'always',
                            'description' => 'Compiled user workspace.',
                            'children' => [],
                        ],
                        [
                            'id' => 'billing-runtime',
                            'type' => 'link',
                            'label' => 'Billing Runtime',
                            'href' => '/admin/struktura/billing-runtime',
                            'icon' => 'heroicon-o-credit-card',
                            'group' => 'Runtime',
                            'target' => 'same-tab',
                            'visibility' => 'always',
                            'description' => 'Injected after save.',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ])
        ->assertRedirect(route('admin.navigation.builder.edit'));

    $menu = NavigationMenu::query()->firstOrFail();

    expect($menu->publishedNodes()[0]['children'])->toHaveCount(2)
        ->and($menu->publishedNodes()[0]['children'][1]['label'])->toBe('Billing Runtime');

    $labels = collect(Filament::getCurrentPanel()->getNavigation())
        ->flatMap(fn (NavigationGroup $group) => flattenNavigationItems($group->getItems()))
        ->pluck('label');

    expect($labels)
        ->toContain('Runtime Pages')
        ->toContain('Billing Runtime');
});

it('renders the db-backed navigation builder workspace', function (): void {
    $user = User::factory()->create();

    $this->seed(StarterNavigationMenusSeeder::class);

    $this->actingAs($user)
        ->get(route('admin.navigation.builder.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NavigationBuilder', false)
            ->where('navigation.name', 'Admin Sidebar')
            ->where('routes.update', route('admin.navigation.builder.update'))
            ->where('initialTree.0.label', 'Runtime Pages')
            ->where('initialTree.0.children.0.label', 'User Management'));
});

/**
 * @param  array<int, NavigationItem>|Arrayable<int, NavigationItem>  $items
 * @return array<int, array{label: string, group: string|UnitEnum|null}>
 */
function flattenNavigationItems(array|Arrayable $items): array
{
    $flattened = [];

    foreach ($items as $item) {
        $flattened[] = [
            'label' => $item->getLabel(),
            'group' => $item->getGroup(),
        ];

        $childItems = $item->getChildItems();

        if ($childItems !== []) {
            $flattened = [
                ...$flattened,
                ...flattenNavigationItems($childItems),
            ];
        }
    }

    return $flattened;
}

<?php

namespace Database\Factories;

use App\Models\NavigationMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationMenu>
 */
class NavigationMenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'admin-sidebar',
            'name' => 'Admin Sidebar',
            'placement' => 'Sidebar',
            'channel' => 'Admin',
            'nodes' => [],
            'draft_nodes' => [],
            'is_active' => true,
        ];
    }
}

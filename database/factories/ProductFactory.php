<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->bothify('PRD-####')),
            'description' => fake()->sentence(),
            'unit_price_cents' => fake()->numberBetween(2500, 150000),
            'currency' => 'USD',
            'is_active' => true,
        ];
    }
}

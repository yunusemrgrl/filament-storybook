<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'reference' => strtoupper(fake()->bothify('PAY-######')),
            'amount_cents' => fake()->numberBetween(10000, 550000),
            'currency' => 'USD',
            'method' => fake()->randomElement(['bank_transfer', 'credit_card', 'cash']),
            'paid_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'notes' => fake()->sentence(),
        ];
    }
}

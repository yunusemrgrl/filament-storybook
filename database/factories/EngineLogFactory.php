<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EngineLog;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EngineLog>
 */
class EngineLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action_name' => fake()->randomElement(['send_invoice', 'record_payment']),
            'event' => fake()->randomElement(['invoice.send', 'invoice.record-payment']),
            'status' => fake()->randomElement(['executed', 'effect_processed']),
            'subject_type' => Invoice::class,
            'subject_id' => Invoice::factory(),
            'actor_type' => User::class,
            'actor_id' => User::factory(),
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'sent'],
            'payload' => ['meta' => ['source' => 'factory']],
        ];
    }
}

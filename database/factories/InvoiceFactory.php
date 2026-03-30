<?php

namespace Database\Factories;

use App\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(InvoiceStatus::cases());
        $subtotal = fake()->numberBetween(10000, 500000);
        $taxTotal = fake()->numberBetween(1000, 50000);

        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => sprintf('INV-%s', fake()->unique()->numerify('######')),
            'status' => $status,
            'issued_at' => now()->subDays(fake()->numberBetween(1, 21)),
            'due_at' => now()->addDays(fake()->numberBetween(7, 30)),
            'sent_at' => in_array($status, [InvoiceStatus::Sent, InvoiceStatus::Paid, InvoiceStatus::Cancelled, InvoiceStatus::Archived], true)
                ? now()->subDays(fake()->numberBetween(1, 7))
                : null,
            'paid_at' => in_array($status, [InvoiceStatus::Paid, InvoiceStatus::Archived], true)
                ? now()->subDays(fake()->numberBetween(0, 5))
                : null,
            'archived_at' => $status === InvoiceStatus::Archived
                ? now()->subHours(fake()->numberBetween(1, 48))
                : null,
            'subtotal_cents' => $subtotal,
            'tax_total_cents' => $taxTotal,
            'total_cents' => $subtotal + $taxTotal,
            'currency' => 'USD',
            'notes' => fake()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Draft,
            'sent_at' => null,
            'paid_at' => null,
            'archived_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Sent,
            'sent_at' => now()->subDay(),
            'paid_at' => null,
            'archived_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Paid,
            'sent_at' => now()->subDays(2),
            'paid_at' => now()->subDay(),
            'archived_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Cancelled,
            'sent_at' => now()->subDays(2),
            'paid_at' => null,
            'archived_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Archived,
            'sent_at' => now()->subDays(4),
            'paid_at' => now()->subDays(2),
            'archived_at' => now()->subDay(),
        ]);
    }
}

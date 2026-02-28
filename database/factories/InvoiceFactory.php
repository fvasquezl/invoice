<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $invoiceDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = fake()->dateTimeBetween($invoiceDate, '+1 month');
        $taxRate = fake()->randomElement([0, 5, 10, 15, 16]);

        return [
            'user_id'        => User::factory(),
            'company_id'     => Company::factory(),
            'client_id'      => Client::factory(),
            'invoice_number' => 'INV-' . now()->year . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_date'   => $invoiceDate,
            'due_date'       => $dueDate,
            'notes'          => fake()->optional(0.5)->sentence(),
            'terms'          => fake()->optional(0.7)->sentence(),
            'subtotal'       => 0,
            'tax_rate'       => $taxRate,
            'tax_amount'     => 0,
            'total'          => 0,
            'status'         => fake()->randomElement(['draft', 'sent', 'paid']),
            'template_id'    => Template::inRandomOrder()->first()?->id,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'sent',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'paid',
        ]);
    }
}

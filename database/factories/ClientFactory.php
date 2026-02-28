<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Client $client) {
            $client->companies()->attach(Company::factory()->create());
        });
    }
}

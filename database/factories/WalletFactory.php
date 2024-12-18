<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'balance' => $this->faker->randomFloat(2, 0, 1000000),
            'currency' => $this->faker->randomElement(['VND', 'USD', 'EUR']),
            'type' => $this->faker->randomElement(['personal']),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}

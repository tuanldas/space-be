<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
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
        $user = User::factory()->create();
        
        return [
            'name' => fake()->word() . ' Wallet',
            'balance' => fake()->randomFloat(2, 0, 10000),
            'currency' => fake()->randomElement(['VND', 'USD', 'EUR', 'JPY', 'GBP']),
            'user_id' => $user->id,
            'created_by' => $user->id,
        ];
    }
    
    /**
     * Indicate that the wallet belongs to a specific user.
     */
    public function forUser(User $user): Factory
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }
    
    /**
     * Indicate that the wallet has a specific creator.
     */
    public function createdBy(User $creator): Factory
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $creator->id,
        ]);
    }
    
    /**
     * Indicate that the wallet uses VND currency.
     */
    public function vnd(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'VND',
        ]);
    }
    
    /**
     * Indicate that the wallet uses USD currency.
     */
    public function usd(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }
}

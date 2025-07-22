<?php

namespace Database\Factories;

use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionCategory>
 */
class TransactionCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransactionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $types = ['income', 'expense'];
        
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement($types),
            'is_default' => false,
            'user_id' => null,
        ];
    }

    /**
     * Indicate that the category is a default category.
     *
     * @return static
     */
    public function default()
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the category belongs to a user.
     *
     * @return static
     */
    public function forUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory(),
                'is_default' => false,
            ];
        });
    }

    /**
     * Indicate that the category is an income category.
     *
     * @return static
     */
    public function income()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    /**
     * Indicate that the category is an expense category.
     *
     * @return static
     */ 
    public function expense()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }
} 
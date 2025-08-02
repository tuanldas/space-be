<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\TransactionCategory;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $wallet = Wallet::factory()->create();
        $transactionType = fake()->randomElement(['income', 'expense', 'transfer']);
        
        $category = TransactionCategory::where('type', $transactionType)->inRandomOrder()->first();
        
        if (!$category) {
            $category = TransactionCategory::factory()->create([
                'type' => $transactionType,
            ]);
        }
        
        return [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'created_by' => $wallet->user_id,
            'amount' => fake()->randomFloat(2, 1, 1000),
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'transaction_type' => $transactionType,
            'description' => fake()->optional(0.7)->sentence(),
        ];
    }
    
    /**
     * Indicate that the transaction belongs to a specific wallet.
     */
    public function forWallet(Wallet $wallet): Factory
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
            'created_by' => $wallet->user_id,
        ]);
    }
    
    /**
     * Indicate that the transaction is of type income.
     */
    public function income(): Factory
    {
        return $this->state(function (array $attributes) {
            $category = TransactionCategory::where('type', 'income')->inRandomOrder()->first()
                ?? TransactionCategory::factory()->create(['type' => 'income']);
                
            return [
                'transaction_type' => 'income',
                'category_id' => $category->id,
            ];
        });
    }
    
    /**
     * Indicate that the transaction is of type expense.
     */
    public function expense(): Factory
    {
        return $this->state(function (array $attributes) {
            $category = TransactionCategory::where('type', 'expense')->inRandomOrder()->first()
                ?? TransactionCategory::factory()->create(['type' => 'expense']);
                
            return [
                'transaction_type' => 'expense',
                'category_id' => $category->id,
            ];
        });
    }
    
    /**
     * Indicate that the transaction is created by a specific user.
     */
    public function createdBy(User $user): Factory
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}

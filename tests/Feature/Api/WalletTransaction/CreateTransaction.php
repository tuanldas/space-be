<?php

namespace Tests\Feature\Api\WalletTransaction;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CreateTransaction extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Wallet $wallet;
    protected TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Test Wallet',
            'balance' => 1000,
        ]);
        
        $this->category = TransactionCategory::factory()->create();
    }

    public function test_user_can_create_income_transaction(): void
    {
        Passport::actingAs($this->user);
        
        $transactionData = [
            'category_id' => $this->category->id,
            'amount' => 500,
            'transaction_date' => now()->format('Y-m-d'),
            'transaction_type' => 'income',
            'description' => 'Test income transaction',
        ];
        
        $response = $this->postJson("/api/wallets/{$this->wallet->id}/transactions", $transactionData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'wallet_id',
                    'category_id',
                    'amount',
                    'transaction_date',
                    'transaction_type',
                    'description',
                ]
            ]);
        
        // Verify wallet balance is updated
        $this->wallet->refresh();
        $this->assertEquals(1500, $this->wallet->balance);
    }
    
    public function test_user_can_create_expense_transaction(): void
    {
        Passport::actingAs($this->user);
        
        $transactionData = [
            'category_id' => $this->category->id,
            'amount' => 300,
            'transaction_date' => now()->format('Y-m-d'),
            'transaction_type' => 'expense',
            'description' => 'Test expense transaction',
        ];
        
        $response = $this->postJson("/api/wallets/{$this->wallet->id}/transactions", $transactionData);
        
        $response->assertStatus(201);
        
        // Verify wallet balance is updated
        $this->wallet->refresh();
        $this->assertEquals(700, $this->wallet->balance);
    }
    
    public function test_user_cannot_create_transaction_for_other_users_wallet(): void
    {
        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);
        
        Passport::actingAs($this->user);
        
        $transactionData = [
            'category_id' => $this->category->id,
            'amount' => 500,
            'transaction_date' => now()->format('Y-m-d'),
            'transaction_type' => 'income',
            'description' => 'Test transaction',
        ];
        
        $response = $this->postJson("/api/wallets/{$otherWallet->id}/transactions", $transactionData);
        
        $response->assertStatus(404);
    }
} 
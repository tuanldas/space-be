<?php

namespace Tests\Feature\Api\WalletTransaction;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetTransactions extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Wallet $wallet;
    protected WalletTransaction $transaction;

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
        
        $category = TransactionCategory::factory()->create();
        $this->transaction = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'amount' => 500,
            'transaction_date' => now(),
            'transaction_type' => 'income',
            'description' => 'Test transaction',
        ]);
    }

    public function test_user_can_view_wallet_transactions(): void
    {
        Passport::actingAs($this->user);
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'wallet_id',
                            'category_id',
                            'amount',
                            'transaction_date',
                            'transaction_type',
                            'description',
                            'created_by',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'current_page',
                    'total'
                ]
            ])
            ->assertJsonPath('data.data.0.id', $this->transaction->id);
    }
    
    public function test_user_cannot_view_transactions_of_other_users_wallet(): void
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions");
        
        $response->assertStatus(404);
    }
    
    public function test_user_can_filter_transactions_by_type(): void
    {
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_type' => 'expense',
        ]);
        
        Passport::actingAs($this->user);
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions?filter[type]=income");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $this->transaction->id);
    }
} 
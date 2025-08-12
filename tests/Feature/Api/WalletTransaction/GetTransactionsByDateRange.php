<?php

namespace Tests\Feature\Api\WalletTransaction;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetTransactionsByDateRange extends TestCase
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
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'category_id' => $this->category->id,
            'transaction_date' => now()->subDays(10),
            'amount' => 100,
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'category_id' => $this->category->id,
            'transaction_date' => now()->subDays(5),
            'amount' => 200,
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'category_id' => $this->category->id,
            'transaction_date' => now(),
            'amount' => 300,
        ]);
    }

    public function test_user_can_filter_transactions_by_date_range(): void
    {
        Passport::actingAs($this->user);
        
        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions?filter[date_between][start]={$startDate}&filter[date_between][end]={$endDate}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 2);
    }
    
    public function test_user_cannot_filter_transactions_of_other_users_wallet(): void
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);
        
        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions?filter[date_between][start]={$startDate}&filter[date_between][end]={$endDate}");
        
        $response->assertStatus(404);
    }
} 
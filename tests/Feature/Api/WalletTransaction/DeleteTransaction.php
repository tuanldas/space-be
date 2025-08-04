<?php

namespace Tests\Feature\Api\WalletTransaction;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DeleteTransaction extends TestCase
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
            'balance' => 1500,
        ]);
        
        $category = TransactionCategory::factory()->create();
        
        $this->transaction = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'amount' => 500,
            'transaction_type' => 'income',
            'description' => 'Test transaction',
        ]);
    }

    public function test_user_can_delete_income_transaction(): void
    {
        Passport::actingAs($this->user);
        
        $response = $this->deleteJson("/api/wallet-transactions/{$this->transaction->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', true);
        
        $this->assertDatabaseMissing('wallet_transactions', [
            'id' => $this->transaction->id
        ]);
        
        $this->wallet->refresh();
        $this->assertEquals(1000, $this->wallet->balance);
    }
    
    public function test_user_can_delete_expense_transaction(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 700,
        ]);
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'created_by' => $this->user->id,
            'amount' => 300,
            'transaction_type' => 'expense',
        ]);
        
        Passport::actingAs($this->user);
        
        $response = $this->deleteJson("/api/wallet-transactions/{$transaction->id}");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('wallet_transactions', [
            'id' => $transaction->id
        ]);
        
        $wallet->refresh();
        $this->assertEquals(1000, $wallet->balance);
    }
    
    public function test_user_cannot_delete_transaction_of_other_users_wallet(): void
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);
        
        $response = $this->deleteJson("/api/wallet-transactions/{$this->transaction->id}");
        
        $response->assertStatus(404);
        
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $this->transaction->id
        ]);
        
        $this->wallet->refresh();
        $this->assertEquals(1500, $this->wallet->balance);
    }
} 
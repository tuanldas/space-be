<?php

namespace Tests\Unit\Repositories;

use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletTransactionRepositoryTest extends RepositoryTestCase
{
    use RefreshDatabase;
    
    protected WalletTransactionRepository $repository;
    protected User $user;
    protected Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new WalletTransactionRepository();
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
    }
    
    public function test_get_transactions_by_wallet_id_returns_paginated_results(): void
    {
        WalletTransaction::factory()->count(5)->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        $otherWallet = Wallet::factory()->create();
        WalletTransaction::factory()->count(2)->create([
            'wallet_id' => $otherWallet->id,
        ]);
        
        $result = $this->repository->getTransactionsByWalletId($this->wallet->id);
        
        $this->assertEquals(5, $result->total());
    }
    
    public function test_get_transactions_by_type_returns_correct_transactions(): void
    {
        WalletTransaction::factory()->count(3)->income()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        WalletTransaction::factory()->count(2)->expense()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        // filter: type = income
        $this->app['request']->query->set('filter', [
            'type' => TransactionType::INCOME->value,
        ]);
        $incomeResult = $this->repository->getTransactions($this->wallet->id);
        $this->assertEquals(3, $incomeResult->total());

        // filter: type = expense
        $this->app['request']->query->set('filter', [
            'type' => TransactionType::EXPENSE->value,
        ]);
        $expenseResult = $this->repository->getTransactions($this->wallet->id);
        $this->assertEquals(2, $expenseResult->total());
    }
    
    public function test_get_transactions_by_date_range_returns_correct_transactions(): void
    {
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(10),
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(5),
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now(),
        ]);
        
        $this->app['request']->query->set('filter', [
            'date_between' => [
                'start' => now()->subDays(7)->format('Y-m-d'),
                'end' => now()->addDay()->format('Y-m-d'),
            ],
        ]);
        $result = $this->repository->getTransactions($this->wallet->id);
        
        $this->assertEquals(2, $result->total());
    }
} 
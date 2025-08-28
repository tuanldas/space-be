<?php

namespace Tests\Unit\Repositories;

use App\Enums\TransactionType;
use App\Models\TransactionCategory;
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

    public function test_search_filter_matches_description_and_amount(): void
    {
        $category = TransactionCategory::factory()->create();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'description' => 'Grocery shopping',
            'amount' => 123.45,
        ]);
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'description' => 'Dinner',
            'amount' => 678.90,
        ]);

        $this->app['request']->query->set('filter', [
            'search' => 'Groc',
        ]);
        $byDescription = $this->repository->getTransactions($this->wallet->id);
        $this->assertEquals(1, $byDescription->total());

        $this->app['request']->query->set('filter', [
            'search' => '678.9',
        ]);
        $byAmount = $this->repository->getTransactions($this->wallet->id);
        $this->assertEquals(1, $byAmount->total());
    }

    public function test_eager_loads_category_and_wallet(): void
    {
        $category = TransactionCategory::factory()->create();
        $tx = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
        ]);

        $result = $this->repository->getTransactions($this->wallet->id);
        $first = $result->items()[0];

        $this->assertTrue($first->relationLoaded('category'));
        $this->assertTrue($first->relationLoaded('wallet'));
    }

    public function test_get_user_transactions_with_filters(): void
    {
        $user2 = User::factory()->create();
        $wallet2 = Wallet::factory()->create([
            'user_id' => $user2->id,
            'created_by' => $user2->id,
        ]);

        WalletTransaction::factory()->count(2)->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'description' => 'abc',
            'transaction_type' => TransactionType::INCOME->value,
        ]);
        WalletTransaction::factory()->count(1)->create([
            'wallet_id' => $wallet2->id,
            'created_by' => $user2->id,
            'description' => 'xyz',
            'transaction_type' => TransactionType::EXPENSE->value,
        ]);

        $this->app['request']->query->set('filter', [
            'type' => TransactionType::INCOME->value,
            'search' => 'ab',
        ]);
        $result = $this->repository->getUserTransactions($this->user->id);
        $this->assertEquals(2, $result->total());
    }

    public function test_sort_by_date_and_amount(): void
    {
        $t1 = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(2),
            'amount' => 100,
        ]);
        $t2 = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(1),
            'amount' => 200,
        ]);
        $t3 = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now(),
            'amount' => 150,
        ]);

        // sort by date asc
        $this->app['request']->query->set('sort', 'transaction_date');
        $byDateAsc = $this->repository->getTransactions($this->wallet->id)->items();
        $this->assertEquals($t1->id, $byDateAsc[0]->id);

        // sort by date desc
        $this->app['request']->query->set('sort', '-transaction_date');
        $byDateDesc = $this->repository->getTransactions($this->wallet->id)->items();
        $this->assertEquals($t3->id, $byDateDesc[0]->id);

        // sort by amount asc
        $this->app['request']->query->set('sort', 'amount');
        $byAmountAsc = $this->repository->getTransactions($this->wallet->id)->items();
        $this->assertEquals(100.00, (float) $byAmountAsc[0]->amount);

        // sort by amount desc
        $this->app['request']->query->set('sort', '-amount');
        $byAmountDesc = $this->repository->getTransactions($this->wallet->id)->items();
        $this->assertEquals(200.00, (float) $byAmountDesc[0]->amount);
    }
} 
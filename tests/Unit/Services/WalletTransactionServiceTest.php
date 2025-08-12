<?php

namespace Tests\Unit\Services;

use App\Enums\TransactionType;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use App\Services\WalletTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class WalletTransactionServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private WalletTransactionRepositoryInterface $transactionRepository;
    private WalletRepositoryInterface $walletRepository;
    private WalletTransactionService $transactionService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionRepository = Mockery::mock(WalletTransactionRepositoryInterface::class);
        $this->walletRepository = Mockery::mock(WalletRepositoryInterface::class);
        
        $this->transactionService = new WalletTransactionService(
            $this->transactionRepository,
            $this->walletRepository
        );
        
        $this->user = User::factory()->create();
        
        // Mock Auth::id() để trả về ID của user
        Auth::shouldReceive('id')->andReturn($this->user->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_transaction_income_updates_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 1000,
        ]);
        
        $category = TransactionCategory::factory()->create();
        
        $transactionData = [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 500,
            'transaction_date' => now()->format('Y-m-d'),
            'transaction_type' => TransactionType::INCOME->value,
            'description' => 'Test income',
            'created_by' => $this->user->id,
        ];
        
        $createdTransaction = new WalletTransaction($transactionData);
        $createdTransaction->id = 'mock-uuid';
        
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
        
        $this->transactionRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($transactionData) {
                return $data['wallet_id'] === $transactionData['wallet_id']
                    && $data['amount'] === $transactionData['amount']
                    && $data['transaction_type'] === $transactionData['transaction_type'];
            }))
            ->andReturn($createdTransaction);
        
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($wallet->id)
            ->andReturn($wallet);
            
        $this->walletRepository
            ->shouldReceive('updateBalance')
            ->once()
            ->with($wallet->id, 500)
            ->andReturn(true);
        
        $result = $this->transactionService->createTransaction($transactionData);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($createdTransaction->id, $result->getData()->id);
    }
    
    public function test_create_transaction_expense_updates_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 1000,
        ]);
        
        $category = TransactionCategory::factory()->create();
        
        $transactionData = [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 300,
            'transaction_date' => now()->format('Y-m-d'),
            'transaction_type' => TransactionType::EXPENSE->value,
            'description' => 'Test expense',
            'created_by' => $this->user->id,
        ];
        
        $createdTransaction = new WalletTransaction($transactionData);
        $createdTransaction->id = 'mock-uuid';
        
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
        
        $this->transactionRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdTransaction);
        
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($wallet->id)
            ->andReturn($wallet);
            
        $this->walletRepository
            ->shouldReceive('updateBalance')
            ->once()
            ->with($wallet->id, -300)
            ->andReturn(true);
        
        $result = $this->transactionService->createTransaction($transactionData);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($createdTransaction->id, $result->getData()->id);
    }
    
    public function test_delete_transaction_income_updates_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 1500,
        ]);
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'created_by' => $this->user->id,
            'amount' => 500,
            'transaction_type' => TransactionType::INCOME->value,
        ]);
        
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
        
        $this->transactionRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($transaction->id)
            ->andReturn($transaction);
            
        $this->transactionRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($transaction->id)
            ->andReturn(true);
            
        // findByUuid for wallet: first call (auth & ownership) returns wallet, second call (fresh) returns updated wallet
        $freshWallet = clone $wallet;
        $freshWallet->balance = 1000;
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->with($wallet->id)
            ->twice()
            ->andReturn($wallet, $freshWallet);
            
        $this->walletRepository
            ->shouldReceive('updateBalance')
            ->once()
            ->with($wallet->id, -500)
            ->andReturn(true);
        
        $result = $this->transactionService->deleteTransaction($wallet->id, $transaction->id);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($transaction->id, $result->getData()['transaction_id']);
        $this->assertEquals(1000, $result->getData()['wallet_balance']);
    }
    
    public function test_delete_transaction_expense_updates_wallet_balance(): void
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
            'transaction_type' => TransactionType::EXPENSE->value,
        ]);
        
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });
        
        $this->transactionRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($transaction->id)
            ->andReturn($transaction);
            
        $this->transactionRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($transaction->id)
            ->andReturn(true);
            
        $freshWallet = clone $wallet;
        $freshWallet->balance = 1000;
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->with($wallet->id)
            ->twice()
            ->andReturn($wallet, $freshWallet);
            
        $this->walletRepository
            ->shouldReceive('updateBalance')
            ->once()
            ->with($wallet->id, 300)
            ->andReturn(true);
        
        $result = $this->transactionService->deleteTransaction($wallet->id, $transaction->id);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($transaction->id, $result->getData()['transaction_id']);
        $this->assertEquals(1000, $result->getData()['wallet_balance']);
    }
    
    public function test_get_transactions_by_wallet_id_returns_paginated_data(): void
    {
        $walletId = 'test-wallet-id';
        $wallet = Wallet::factory()->make([
            'id' => $walletId,
            'user_id' => $this->user->id,
        ]);
        
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($walletId)
            ->andReturn($wallet);
            
        $expectedTransactions = WalletTransaction::factory()->count(3)->make([
            'wallet_id' => $walletId,
        ]);
        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $expectedTransactions,
            count($expectedTransactions),
            15,
            1
        );
        
        $this->transactionRepository
            ->shouldReceive('getTransactions')
            ->once()
            ->with($walletId)
            ->andReturn($mockPaginator);
        
        $result = $this->transactionService->getTransactions($walletId);
        
        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result->getData());
        $this->assertEquals(3, $result->getData()->count());
    }
} 
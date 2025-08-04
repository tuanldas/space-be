<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\Wallet;
use App\Repositories\WalletRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletRepositoryTest extends RepositoryTestCase
{
    use RefreshDatabase;
    
    protected WalletRepository $repository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new WalletRepository();
        $this->user = User::factory()->create();
    }
    
    public function test_get_wallets_summary_by_user_id_returns_correct_wallets(): void
    {
        $wallet1 = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Tiền mặt',
            'balance' => 1000000,
            'currency' => 'VND',
        ]);
        
        $wallet2 = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Tài khoản ngân hàng',
            'balance' => 5000000,
            'currency' => 'VND',
        ]);
        
        $otherUser = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'name' => 'Other User Wallet',
            'balance' => 2000000,
            'currency' => 'VND',
        ]);
        
        $result = $this->repository->getWalletsSummaryByUserId($this->user->id);
        
        $this->assertCount(2, $result);
        
        $resultIds = [];
        foreach ($result as $item) {
            $resultIds[] = $item->id;
        }
        
        $this->assertContains($wallet1->id, $resultIds);
        $this->assertContains($wallet2->id, $resultIds);
        
        $foundWallet1 = null;
        foreach ($result as $item) {
            if ($item->id === $wallet1->id) {
                $foundWallet1 = $item;
                break;
            }
        }
        
        $this->assertNotNull($foundWallet1);
        $this->assertEquals('Tiền mặt', $foundWallet1->name);
        $this->assertEquals(1000000, $foundWallet1->balance);
        $this->assertEquals('VND', $foundWallet1->currency);
        
        $firstItem = $result[0];
        $this->assertIsObject($firstItem);
        $this->assertNotNull($firstItem->id);
        $this->assertNotNull($firstItem->name);
        $this->assertNotNull($firstItem->balance);
        $this->assertNotNull($firstItem->currency);
    }
    
    public function test_get_wallets_by_user_id_returns_paginated_results(): void
    {
        Wallet::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
        
        $result = $this->repository->getWalletsByUserId($this->user->id);
        
        $this->assertEquals(5, $result->total());
    }
    
    public function test_update_balance_correctly_modifies_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 1000,
        ]);
        
        $success = $this->repository->updateBalance($wallet->id, 500);
        $this->assertTrue($success);
        
        $updatedWallet = Wallet::find($wallet->id);
        $this->assertEquals(1500, $updatedWallet->balance);
        
        $success = $this->repository->updateBalance($wallet->id, -300);
        $this->assertTrue($success);
        
        $updatedWallet->refresh();
        $this->assertEquals(1200, $updatedWallet->balance);
    }
} 
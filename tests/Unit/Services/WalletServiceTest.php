<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private WalletRepositoryInterface $walletRepository;
    private WalletService $walletService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletRepository = Mockery::mock(WalletRepositoryInterface::class);
        $this->walletService = new WalletService($this->walletRepository);
        
        $this->user = User::factory()->create();
        Auth::shouldReceive('id')->andReturn($this->user->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_wallets_summary_for_sidebar_returns_correct_data(): void
    {
        $walletData = collect([
            (object)[
                'id' => 'uuid1',
                'name' => 'Tiền mặt',
                'balance' => 1000000,
                'currency' => 'VND',
            ],
            (object)[
                'id' => 'uuid2',
                'name' => 'Tài khoản ngân hàng',
                'balance' => 5000000,
                'currency' => 'VND',
            ],
        ]);
        
        $this->walletRepository
            ->shouldReceive('getWalletsSummaryByUserId')
            ->once()
            ->with($this->user->id)
            ->andReturn($walletData);
        
        $result = $this->walletService->getWalletsSummaryForSidebar();
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Tiền mặt', $result[0]->name);
        $this->assertEquals(1000000, $result[0]->balance);
        $this->assertEquals('VND', $result[0]->currency);
    }
    
    public function test_get_wallets_summary_for_sidebar_handles_exception(): void
    {
        $this->walletRepository
            ->shouldReceive('getWalletsSummaryByUserId')
            ->once()
            ->with($this->user->id)
            ->andThrow(new \Exception('Database error'));
        
        $result = $this->walletService->getWalletsSummaryForSidebar();
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
    
    public function test_get_wallet_by_id_returns_wallet_for_current_user(): void
    {
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Test Wallet',
        ]);
        
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($wallet->id)
            ->andReturn($wallet);
        
        $result = $this->walletService->getWalletById($wallet->id);
        
        $this->assertNotNull($result);
        $this->assertEquals($wallet->id, $result->id);
        $this->assertEquals('Test Wallet', $result->name);
    }
    
    public function test_get_wallet_by_id_returns_null_for_another_users_wallet(): void
    {
        $otherUser = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'name' => 'Another User Wallet',
        ]);
        
        $this->walletRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($wallet->id)
            ->andReturn($wallet);
        
        $result = $this->walletService->getWalletById($wallet->id);
        
        $this->assertNull($result);
    }
} 
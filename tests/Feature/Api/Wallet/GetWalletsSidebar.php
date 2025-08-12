<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetWalletsSidebar extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that an authenticated user can get wallets summary for sidebar.
     */
    public function test_user_can_get_wallets_summary_for_sidebar(): void
    {
        Passport::actingAs($this->user);
        
        // Tạo các ví mẫu với thông tin khác nhau
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
        
        $wallet3 = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'USD Account',
            'balance' => 1000,
            'currency' => 'USD',
        ]);
        
        // Tạo ví cho người dùng khác (không nên xuất hiện trong kết quả)
        $otherUser = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'name' => 'Other User Wallet',
            'balance' => 2000000,
            'currency' => 'VND',
        ]);
        
        // Gọi API wallets-sidebar
        $response = $this->getJson('/api/wallets-sidebar');
        
        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'balance',
                        'currency',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        // Kiểm tra số lượng ví
        $this->assertCount(3, $response->json('data'));
        
        // Kiểm tra ví cụ thể có trong kết quả
        $responseData = $response->json('data');
        $walletIds = array_column($responseData, 'id');
        
        $this->assertContains($wallet1->id, $walletIds);
        $this->assertContains($wallet2->id, $walletIds);
        $this->assertContains($wallet3->id, $walletIds);
        
        // Kiểm tra thông tin của một ví cụ thể
        $foundWallet1 = null;
        foreach ($responseData as $wallet) {
            if ($wallet['id'] === $wallet1->id) {
                $foundWallet1 = $wallet;
                break;
            }
        }
        
        $this->assertNotNull($foundWallet1);
        $this->assertEquals('Tiền mặt', $foundWallet1['name']);
        $this->assertEquals('1000000.00', $foundWallet1['balance']);
        $this->assertEquals('VND', $foundWallet1['currency']);
    }
} 
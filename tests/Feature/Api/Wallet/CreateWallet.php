<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CreateWallet extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that an authenticated user can create a new wallet.
     */
    public function test_user_can_create_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $walletData = [
            'name' => 'Test Wallet',
            'balance' => 1000,
            'currency' => 'VND',
        ];
        
        $response = $this->postJson('/api/wallets', $walletData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'balance',
                    'currency',
                    'user_id',
                    'created_by',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.wallet.created'),
                'data' => [
                    'name' => $walletData['name'],
                    'balance' => (string) $walletData['balance'],
                    'currency' => $walletData['currency'],
                    'user_id' => $this->user->id,
                    'created_by' => $this->user->id,
                ],
            ]);
        
        $this->assertDatabaseHas('wallets', [
            'name' => $walletData['name'],
            'currency' => $walletData['currency'],
            'user_id' => $this->user->id,
        ]);
    }
} 
<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetWallets extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that an authenticated user can view their wallets.
     */
    public function test_user_can_view_wallets(): void
    {
        Passport::actingAs($this->user);
        
        // Create some wallets for the user
        Wallet::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
        
        $response = $this->getJson('/api/wallets');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'balance',
                            'currency',
                            'user_id',
                            'created_by',
                        ]
                    ],
                    'current_page',
                    'total',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        $this->assertEquals(3, count($response->json('data.data')));
    }
    
    /**
     * Test that an authenticated user can view a specific wallet.
     */
    public function test_user_can_view_wallet_details(): void
    {
        Passport::actingAs($this->user);
        
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'My Special Wallet',
            'currency' => 'USD',
        ]);
        
        $response = $this->getJson("/api/wallets/{$wallet->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
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
                'data' => [
                    'id' => $wallet->id,
                    'name' => 'My Special Wallet',
                    'currency' => 'USD',
                    'user_id' => $this->user->id,
                ],
            ]);
    }
    
    /**
     * Test that a user cannot access another user's wallet.
     */
    public function test_user_cannot_access_another_users_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        $wallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);
        
        $response = $this->getJson("/api/wallets/{$wallet->id}");
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('messages.wallet.not_found'),
            ]);
    }
} 
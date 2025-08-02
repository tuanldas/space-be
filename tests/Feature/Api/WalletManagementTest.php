<?php

namespace Tests\Feature\Feature\Api;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class WalletManagementTest extends TestCase
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
     * Test that an authenticated user can update their wallet.
     */
    public function test_user_can_update_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Original Wallet Name',
        ]);
        
        $updateData = [
            'name' => 'Updated Wallet Name',
            'currency' => 'EUR',
        ];
        
        $response = $this->putJson("/api/wallets/{$wallet->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.wallet.updated'),
                'data' => [
                    'id' => $wallet->id,
                    'name' => 'Updated Wallet Name',
                    'currency' => 'EUR',
                ],
            ]);
        
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'name' => 'Updated Wallet Name',
            'currency' => 'EUR',
        ]);
    }
    
    /**
     * Test that an authenticated user can delete their wallet.
     */
    public function test_user_can_delete_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/wallets/{$wallet->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.wallet.deleted'),
            ]);
        
        // Kiểm tra soft delete
        $this->assertSoftDeleted('wallets', [
            'id' => $wallet->id,
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
    
    /**
     * Test that a user cannot update another user's wallet.
     */
    public function test_user_cannot_update_another_users_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        $wallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'name' => 'Original Name',
        ]);
        
        $response = $this->putJson("/api/wallets/{$wallet->id}", [
            'name' => 'Attempted Update',
        ]);
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('messages.wallet.not_found'),
            ]);
        
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'name' => 'Original Name',
        ]);
    }
    
    /**
     * Test that a user cannot delete another user's wallet.
     */
    public function test_user_cannot_delete_another_users_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        $wallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);
        
        $response = $this->deleteJson("/api/wallets/{$wallet->id}");
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('messages.wallet.not_found'),
            ]);
        
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'deleted_at' => null,
        ]);
    }
}

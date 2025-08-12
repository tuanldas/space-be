<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UpdateWallet extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
} 
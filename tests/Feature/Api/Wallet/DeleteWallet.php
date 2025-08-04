<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DeleteWallet extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
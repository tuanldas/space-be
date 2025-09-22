<?php

namespace Tests\Feature\Api\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetWalletOptions extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_get_wallet_options_with_search_and_limit(): void
    {
        Passport::actingAs($this->user);

        // Create wallets for current user
        Wallet::factory()->create(['user_id' => $this->user->id, 'created_by' => $this->user->id, 'name' => 'Ví tiền mặt']);
        Wallet::factory()->create(['user_id' => $this->user->id, 'created_by' => $this->user->id, 'name' => 'Ví ngân hàng']);
        Wallet::factory()->create(['user_id' => $this->user->id, 'created_by' => $this->user->id, 'name' => 'Savings USD']);

        // Another user's wallet should not appear
        $other = User::factory()->create();
        Wallet::factory()->create(['user_id' => $other->id, 'created_by' => $other->id, 'name' => 'Other Wallet']);

        // Call API with search "vi" (matches two VN wallets) and limit 1
        $response = $this->getJson('/api/wallets-options?search=vi&limit=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name']
                ]
            ])
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(1, $data); // limited by 1
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);

        // Without search, expect only current user's wallets (max 3 here)
        $responseAll = $this->getJson('/api/wallets-options?limit=5');
        $responseAll->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($responseAll->json('data')));
    }
} 
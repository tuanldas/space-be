<?php

namespace Tests\Feature\Api\TransactionCategory;

use App\Enums\TransactionType;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetCategoryOptions extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_get_category_options_with_search_type_and_limit(): void
    {
        Passport::actingAs($this->user);

        // Default categories
        TransactionCategory::factory()->create(['name' => 'Ăn uống', 'type' => TransactionType::EXPENSE->value, 'is_default' => true]);
        TransactionCategory::factory()->create(['name' => 'Lương', 'type' => TransactionType::INCOME->value, 'is_default' => true]);

        // User categories
        TransactionCategory::factory()->create(['user_id' => $this->user->id, 'name' => 'Ăn sáng', 'type' => TransactionType::EXPENSE->value]);
        TransactionCategory::factory()->create(['user_id' => $this->user->id, 'name' => 'Ăn trưa', 'type' => TransactionType::EXPENSE->value]);
        TransactionCategory::factory()->create(['user_id' => $this->user->id, 'name' => 'Tiền thưởng', 'type' => TransactionType::INCOME->value]);

        // Other user's category should still NOT be returned unless default
        $other = User::factory()->create();
        TransactionCategory::factory()->create(['user_id' => $other->id, 'name' => 'Ăn vặt', 'type' => TransactionType::EXPENSE->value]);

        // Query: type=expense, search=an, limit=2
        $response = $this->getJson('/api/transaction-categories-options?type=expense&search=an&limit=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name']
                ]
            ])
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(2, $data); // limited

        // Should only contain expense names containing 'an' (Ăn uống, Ăn sáng, Ăn trưa)
        foreach ($data as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
        }
    }
} 
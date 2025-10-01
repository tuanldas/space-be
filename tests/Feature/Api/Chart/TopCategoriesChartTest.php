<?php

namespace Tests\Feature\Api\Chart;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\TransactionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Carbon\Carbon;

class TopCategoriesChartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_get_top_categories_for_current_month(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        $category1 = TransactionCategory::factory()->create(['type' => 'expense', 'name' => 'Ăn uống']);
        $category2 = TransactionCategory::factory()->create(['type' => 'expense', 'name' => 'Di chuyển']);
        $category3 = TransactionCategory::factory()->create(['type' => 'income', 'name' => 'Lương']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category1->id,
            'transaction_type' => 'expense',
            'amount' => 500000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category1->id,
            'transaction_type' => 'expense',
            'amount' => 300000,
            'transaction_date' => $currentMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category2->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(3),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category3->id,
            'transaction_type' => 'income',
            'amount' => 7000000,
            'transaction_date' => $currentMonth->copy()->addDays(1),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'month',
                    'categories' => [
                        '*' => [
                            'category_id',
                            'category_name',
                            'category_type',
                            'category_image',
                            'total',
                            'transaction_count',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => $currentMonth->format('Y-m'),
                ],
            ]);

        $categories = $response->json('data.categories');
        
        $this->assertEquals($category3->id, $categories[0]['category_id']);
        $this->assertEquals(7000000, $categories[0]['total']);
        $this->assertEquals('income', $categories[0]['category_type']);
        
        $this->assertEquals($category1->id, $categories[1]['category_id']);
        $this->assertEquals(800000, $categories[1]['total']);
        $this->assertEquals(2, $categories[1]['transaction_count']);
        $this->assertEquals('expense', $categories[1]['category_type']);
        
        $this->assertEquals($category2->id, $categories[2]['category_id']);
        $this->assertEquals(200000, $categories[2]['total']);
    }

    public function test_get_top_categories_for_specific_month(): void
    {
        Passport::actingAs($this->user);

        $targetMonth = Carbon::parse('2025-09-01');
        $category = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 500000,
            'transaction_date' => $targetMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories?month=2025-09');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => '2025-09',
                ],
            ]);

        $categories = $response->json('data.categories');
        $this->assertCount(1, $categories);
        $this->assertEquals(500000, $categories[0]['total']);
    }

    public function test_get_top_categories_filtered_by_wallet(): void
    {
        Passport::actingAs($this->user);

        $wallet2 = Wallet::factory()->create(['user_id' => $this->user->id]);
        $currentMonth = Carbon::now()->startOfMonth();
        $category = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet2->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories?wallet_id=' . $this->wallet->id);

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(1, $categories);
        $this->assertEquals(100000, $categories[0]['total']);
    }

    public function test_limit_parameter_works(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = 1; $i <= 10; $i++) {
            $category = TransactionCategory::factory()->create(['type' => 'expense', 'name' => "Category $i"]);
            
            WalletTransaction::factory()->create([
                'wallet_id' => $this->wallet->id,
                'category_id' => $category->id,
                'transaction_type' => 'expense',
                'amount' => 100000 * $i,
                'transaction_date' => $currentMonth->copy()->addDays(2),
                'created_by' => $this->user->id,
            ]);
        }

        $response = $this->getJson('/api/charts/top-categories?limit=3');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(3, $categories);
        
        $this->assertEquals(1000000, $categories[0]['total']);
        $this->assertEquals(900000, $categories[1]['total']);
        $this->assertEquals(800000, $categories[2]['total']);
    }

    public function test_includes_both_income_and_expense(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();
        $incomeCategory = TransactionCategory::factory()->create(['type' => 'income']);
        $expenseCategory = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 5000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 3000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(2, $categories);
        
        $types = array_column($categories, 'category_type');
        $this->assertContains('income', $types);
        $this->assertContains('expense', $types);
    }

    public function test_categories_sorted_by_total_descending(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();
        
        $category1 = TransactionCategory::factory()->create(['type' => 'expense']);
        $category2 = TransactionCategory::factory()->create(['type' => 'expense']);
        $category3 = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category1->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category2->id,
            'transaction_type' => 'expense',
            'amount' => 500000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category3->id,
            'transaction_type' => 'expense',
            'amount' => 300000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        
        $this->assertEquals(500000, $categories[0]['total']);
        $this->assertEquals(300000, $categories[1]['total']);
        $this->assertEquals(200000, $categories[2]['total']);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/charts/top-categories');

        $response->assertStatus(401);
    }

    public function test_validates_month_format(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/top-categories?month=2025-13');

        $response->assertStatus(422);
    }

    public function test_validates_wallet_id_exists(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/top-categories?wallet_id=invalid-uuid');

        $response->assertStatus(422);
    }

    public function test_validates_limit_range(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/top-categories?limit=0');
        $response->assertStatus(422);

        $response = $this->getJson('/api/charts/top-categories?limit=25');
        $response->assertStatus(422);
    }

    public function test_cannot_access_other_users_wallet(): void
    {
        Passport::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/charts/top-categories?wallet_id=' . $otherWallet->id);

        $response->assertStatus(404);
    }

    public function test_returns_empty_array_for_month_without_transactions(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/top-categories?month=2024-01');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(0, $categories);
    }

    public function test_aggregates_same_category_correctly(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();
        $category = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->addDays(1),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $category->id,
            'transaction_type' => 'expense',
            'amount' => 150000,
            'transaction_date' => $currentMonth->copy()->addDays(10),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/top-categories');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(1, $categories);
        $this->assertEquals(450000, $categories[0]['total']);
        $this->assertEquals(3, $categories[0]['transaction_count']);
    }
}


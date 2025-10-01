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

class MonthlyExpensesChartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;
    private TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create(['user_id' => $this->user->id]);
        $this->category = TransactionCategory::factory()->create(['type' => 'expense']);
    }

    public function test_get_monthly_expenses_for_current_month(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(10),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/monthly-expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'month',
                    'weekly_expenses' => [
                        '*' => [
                            'week',
                            'date_range',
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

        $weeklyExpenses = $response->json('data.weekly_expenses');
        $this->assertGreaterThanOrEqual(4, count($weeklyExpenses));
        $this->assertLessThanOrEqual(6, count($weeklyExpenses));
        
        $totalExpenses = array_sum(array_column($weeklyExpenses, 'total'));
        $this->assertEquals(300000, $totalExpenses);
        
        $totalCount = array_sum(array_column($weeklyExpenses, 'transaction_count'));
        $this->assertEquals(2, $totalCount);
    }

    public function test_get_monthly_expenses_for_specific_month(): void
    {
        Passport::actingAs($this->user);

        $targetMonth = Carbon::parse('2025-09-01');

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 500000,
            'transaction_date' => $targetMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/monthly-expenses?month=2025-09');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => '2025-09',
                ],
            ]);

        $weeklyExpenses = $response->json('data.weekly_expenses');
        $this->assertEquals(500000, $weeklyExpenses[0]['total']);
    }

    public function test_get_monthly_expenses_filtered_by_wallet(): void
    {
        Passport::actingAs($this->user);

        $wallet2 = Wallet::factory()->create(['user_id' => $this->user->id]);
        $currentMonth = Carbon::now()->startOfMonth();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet2->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/monthly-expenses?wallet_id=' . $this->wallet->id);

        $response->assertStatus(200);

        $weeklyExpenses = $response->json('data.weekly_expenses');
        $this->assertEquals(100000, $weeklyExpenses[0]['total']);
        $this->assertEquals(1, $weeklyExpenses[0]['transaction_count']);
    }

    public function test_income_transactions_are_excluded(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'income',
            'amount' => 500000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/monthly-expenses');

        $response->assertStatus(200);

        $weeklyExpenses = $response->json('data.weekly_expenses');
        $this->assertEquals(100000, $weeklyExpenses[0]['total']);
        $this->assertEquals(1, $weeklyExpenses[0]['transaction_count']);
    }

    public function test_weekly_expenses_divided_correctly(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 100000,
            'transaction_date' => $currentMonth->copy()->day(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 200000,
            'transaction_date' => $currentMonth->copy()->day(10),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 150000,
            'transaction_date' => $currentMonth->copy()->day(18),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->category->id,
            'transaction_type' => 'expense',
            'amount' => 300000,
            'transaction_date' => $currentMonth->copy()->day(25),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/monthly-expenses');

        $response->assertStatus(200);

        $weeklyExpenses = $response->json('data.weekly_expenses');
        
        $this->assertGreaterThanOrEqual(4, count($weeklyExpenses));
        $this->assertLessThanOrEqual(6, count($weeklyExpenses));
        
        $totalExpenses = array_sum(array_column($weeklyExpenses, 'total'));
        $this->assertEquals(750000, $totalExpenses);
        
        $totalCount = array_sum(array_column($weeklyExpenses, 'transaction_count'));
        $this->assertEquals(4, $totalCount);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/charts/monthly-expenses');

        $response->assertStatus(401);
    }

    public function test_validates_month_format(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/monthly-expenses?month=2025-13');

        $response->assertStatus(422);
    }

    public function test_validates_wallet_id_exists(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/monthly-expenses?wallet_id=non-existent-uuid');

        $response->assertStatus(422);
    }

    public function test_cannot_access_other_users_wallet(): void
    {
        Passport::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/charts/monthly-expenses?wallet_id=' . $otherWallet->id);

        $response->assertStatus(404);
    }

    public function test_returns_zero_expenses_for_month_without_transactions(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/monthly-expenses?month=2024-01');

        $response->assertStatus(200);

        $weeklyExpenses = $response->json('data.weekly_expenses');
        
        foreach ($weeklyExpenses as $week) {
            $this->assertEquals(0, $week['total']);
            $this->assertEquals(0, $week['transaction_count']);
        }
    }
}


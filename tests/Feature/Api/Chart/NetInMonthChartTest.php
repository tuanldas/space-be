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

class NetInMonthChartTest extends TestCase
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

    public function test_get_net_for_current_month(): void
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
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 3000000,
            'transaction_date' => $currentMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 2000000,
            'transaction_date' => $currentMonth->copy()->addDays(3),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 1500000,
            'transaction_date' => $currentMonth->copy()->addDays(7),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/net-in-month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'month',
                    'total_income',
                    'total_expense',
                    'net',
                    'income_count',
                    'expense_count',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => $currentMonth->format('Y-m'),
                    'total_income' => 8000000,
                    'total_expense' => 3500000,
                    'net' => 4500000,
                    'income_count' => 2,
                    'expense_count' => 2,
                ],
            ]);
    }

    public function test_get_net_for_specific_month(): void
    {
        Passport::actingAs($this->user);

        $targetMonth = Carbon::parse('2025-09-01');

        $incomeCategory = TransactionCategory::factory()->create(['type' => 'income']);
        $expenseCategory = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 10000000,
            'transaction_date' => $targetMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 4000000,
            'transaction_date' => $targetMonth->copy()->addDays(10),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/net-in-month?month=2025-09');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => '2025-09',
                    'total_income' => 10000000,
                    'total_expense' => 4000000,
                    'net' => 6000000,
                    'income_count' => 1,
                    'expense_count' => 1,
                ],
            ]);
    }

    public function test_get_net_filtered_by_wallet(): void
    {
        Passport::actingAs($this->user);

        $wallet2 = Wallet::factory()->create(['user_id' => $this->user->id]);
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
            'amount' => 2000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet2->id,
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 10000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet2->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 3000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/net-in-month?wallet_id=' . $this->wallet->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_income' => 5000000,
                    'total_expense' => 2000000,
                    'net' => 3000000,
                    'income_count' => 1,
                    'expense_count' => 1,
                ],
            ]);
    }

    public function test_net_can_be_negative(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        $incomeCategory = TransactionCategory::factory()->create(['type' => 'income']);
        $expenseCategory = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 2000000,
            'transaction_date' => $currentMonth->copy()->addDays(2),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 5000000,
            'transaction_date' => $currentMonth->copy()->addDays(3),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/net-in-month');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_income' => 2000000,
                    'total_expense' => 5000000,
                    'net' => -3000000,
                ],
            ]);
    }

    public function test_only_includes_transactions_in_month(): void
    {
        Passport::actingAs($this->user);

        $targetMonth = Carbon::parse('2025-09-01');
        $incomeCategory = TransactionCategory::factory()->create(['type' => 'income']);
        $expenseCategory = TransactionCategory::factory()->create(['type' => 'expense']);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $incomeCategory->id,
            'transaction_type' => 'income',
            'amount' => 5000000,
            'transaction_date' => $targetMonth->copy()->addDays(5),
            'created_by' => $this->user->id,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $expenseCategory->id,
            'transaction_type' => 'expense',
            'amount' => 2000000,
            'transaction_date' => $targetMonth->copy()->addMonths(1)->addDays(5),
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/charts/net-in-month?month=2025-09');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_income' => 5000000,
                    'total_expense' => 0,
                    'net' => 5000000,
                    'income_count' => 1,
                    'expense_count' => 0,
                ],
            ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/charts/net-in-month');

        $response->assertStatus(401);
    }

    public function test_validates_month_format(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/net-in-month?month=2025-13');

        $response->assertStatus(422);
    }

    public function test_validates_wallet_id_exists(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/net-in-month?wallet_id=invalid-uuid');

        $response->assertStatus(422);
    }

    public function test_cannot_access_other_users_wallet(): void
    {
        Passport::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/charts/net-in-month?wallet_id=' . $otherWallet->id);

        $response->assertStatus(404);
    }

    public function test_returns_zero_for_month_without_transactions(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/charts/net-in-month?month=2024-01');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_income' => 0,
                    'total_expense' => 0,
                    'net' => 0,
                    'income_count' => 0,
                    'expense_count' => 0,
                ],
            ]);
    }

    public function test_aggregates_multiple_transactions_correctly(): void
    {
        Passport::actingAs($this->user);

        $currentMonth = Carbon::now()->startOfMonth();

        $incomeCategory = TransactionCategory::factory()->create(['type' => 'income']);
        $expenseCategory = TransactionCategory::factory()->create(['type' => 'expense']);

        for ($i = 1; $i <= 5; $i++) {
            WalletTransaction::factory()->create([
                'wallet_id' => $this->wallet->id,
                'category_id' => $incomeCategory->id,
                'transaction_type' => 'income',
                'amount' => 1000000,
                'transaction_date' => $currentMonth->copy()->addDays($i),
                'created_by' => $this->user->id,
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            WalletTransaction::factory()->create([
                'wallet_id' => $this->wallet->id,
                'category_id' => $expenseCategory->id,
                'transaction_type' => 'expense',
                'amount' => 500000,
                'transaction_date' => $currentMonth->copy()->addDays($i),
                'created_by' => $this->user->id,
            ]);
        }

        $response = $this->getJson('/api/charts/net-in-month');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_income' => 5000000,
                    'total_expense' => 1500000,
                    'net' => 3500000,
                    'income_count' => 5,
                    'expense_count' => 3,
                ],
            ]);
    }
}


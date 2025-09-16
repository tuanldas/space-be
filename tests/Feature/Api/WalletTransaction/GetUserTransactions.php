<?php

namespace Tests\Feature\Api\WalletTransaction;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetUserTransactions extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Wallet $wallet1;
    protected Wallet $wallet2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->wallet1 = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Wallet 1',
        ]);
        $this->wallet2 = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'name' => 'Wallet 2',
        ]);

        $category = TransactionCategory::factory()->create();

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet1->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'amount' => 500,
            'transaction_date' => now()->subDays(2),
            'transaction_type' => 'income',
            'description' => 'Salary',
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet2->id,
            'category_id' => $category->id,
            'created_by' => $this->user->id,
            'amount' => 200,
            'transaction_date' => now()->subDays(1),
            'transaction_type' => 'expense',
            'description' => 'Coffee',
        ]);
    }

    public function test_user_can_list_their_transactions_with_category_and_wallet(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/user/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'wallet_id',
                            'category_id',
                            'amount',
                            'transaction_date',
                            'transaction_type',
                            'description',
                            'created_by',
                            'category' => [
                                'name', 'type', 'image'
                            ],
                            'wallet' => [
                                'name', 'balance', 'currency'
                            ]
                        ]
                    ],
                    'current_page',
                    'total'
                ]
            ])
            ->assertJsonMissingPath('data.data.0.created_at')
            ->assertJsonMissingPath('data.data.0.updated_at');
    }

    public function test_user_can_filter_by_type_and_search_and_wallet(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson("/api/user/transactions?filter[type]=income&filter[search]=Sala&filter[wallet_id]={$this->wallet1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 1);
    }

    public function test_user_can_filter_by_date_range(): void
    {
        Passport::actingAs($this->user);

        $startDate = now()->subDays(3)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->getJson("/api/user/transactions?filter[date_between][start]={$startDate}&filter[date_between][end]={$endDate}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'total'
                ]
            ]);
    }

    public function test_user_can_filter_by_single_category_and_multiple_categories(): void
    {
        Passport::actingAs($this->user);

        $catA = TransactionCategory::factory()->create();
        $catB = TransactionCategory::factory()->create();
        $catC = TransactionCategory::factory()->create();

        // Create more transactions across categories
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet1->id,
            'category_id' => $catA->id,
            'created_by' => $this->user->id,
            'amount' => 100,
            'transaction_date' => now()->subHours(5),
            'transaction_type' => 'expense',
        ]);
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet2->id,
            'category_id' => $catB->id,
            'created_by' => $this->user->id,
            'amount' => 300,
            'transaction_date' => now()->subHours(4),
            'transaction_type' => 'expense',
        ]);

        // Single category filter
        $resSingle = $this->getJson("/api/user/transactions?filter[category_id]={$catA->id}");
        $resSingle->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $resSingle->json('data.total'));

        // Multiple categories filter as array
        $resMulti = $this->getJson('/api/user/transactions?filter[category_ids][]=' . $catA->id . '&filter[category_ids][]=' . $catB->id);
        $resMulti->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $resMulti->json('data.total'));

        // Multiple categories filter as comma string
        $resComma = $this->getJson('/api/user/transactions?filter[category_ids]=' . $catA->id . ',' . $catC->id);
        $resComma->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $resComma->json('data.total'));
    }
} 
<?php

namespace Tests\Feature\Feature\Api;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class WalletTransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Wallet $wallet;
    protected TransactionCategory $incomeCategory;
    protected TransactionCategory $expenseCategory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'balance' => 5000,
        ]);
        $this->incomeCategory = TransactionCategory::factory()->create([
            'type' => 'income',
            'user_id' => $this->user->id,
        ]);
        $this->expenseCategory = TransactionCategory::factory()->create([
            'type' => 'expense',
            'user_id' => $this->user->id,
        ]);
    }
    
    /**
     * Test that a user can create an income transaction.
     */
    public function test_user_can_create_income_transaction(): void
    {
        Passport::actingAs($this->user);
        
        $transactionData = [
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'transaction_date' => now()->toDateTimeString(),
            'transaction_type' => 'income',
            'description' => 'Test income transaction',
        ];
        
        $response = $this->postJson('/api/transactions', $transactionData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'wallet_id',
                    'category_id',
                    'amount',
                    'transaction_date',
                    'transaction_type',
                    'description',
                    'created_by',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.wallet_transaction.created'),
                'data' => [
                    'wallet_id' => $this->wallet->id,
                    'category_id' => $this->incomeCategory->id,
                    'amount' => (string) $transactionData['amount'],
                    'transaction_type' => 'income',
                    'description' => 'Test income transaction',
                    'created_by' => $this->user->id,
                ],
            ]);
        
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => $transactionData['amount'],
            'transaction_type' => 'income',
            'description' => 'Test income transaction',
        ]);
        
        // Kiểm tra cập nhật số dư ví
        $this->assertDatabaseHas('wallets', [
            'id' => $this->wallet->id,
            'balance' => 6000, // 5000 + 1000
        ]);
    }
    
    /**
     * Test that a user can create an expense transaction.
     */
    public function test_user_can_create_expense_transaction(): void
    {
        Passport::actingAs($this->user);
        
        $transactionData = [
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 2000,
            'transaction_date' => now()->toDateTimeString(),
            'transaction_type' => 'expense',
            'description' => 'Test expense transaction',
        ];
        
        $response = $this->postJson('/api/transactions', $transactionData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.wallet_transaction.created'),
            ]);
        
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => $transactionData['amount'],
            'transaction_type' => 'expense',
        ]);
        
        // Kiểm tra cập nhật số dư ví (trừ đi khi chi tiêu)
        $this->assertDatabaseHas('wallets', [
            'id' => $this->wallet->id,
            'balance' => 3000, // 5000 - 2000
        ]);
    }
    
    /**
     * Test that a user can view transactions for their wallet.
     */
    public function test_user_can_view_wallet_transactions(): void
    {
        Passport::actingAs($this->user);
        
        // Tạo các giao dịch mẫu
        WalletTransaction::factory()->count(5)->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions");
        
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
                        ]
                    ],
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        $this->assertEquals(5, count($response->json('data.data')));
    }
    
    /**
     * Test that a user can view transaction details.
     */
    public function test_user_can_view_transaction_details(): void
    {
        Passport::actingAs($this->user);
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'amount' => 1500,
            'transaction_type' => 'income',
            'description' => 'Test transaction details',
        ]);
        
        $response = $this->getJson("/api/transactions/{$transaction->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'wallet_id',
                    'category_id',
                    'amount',
                    'transaction_date',
                    'transaction_type',
                    'description',
                    'created_by',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'wallet_id' => $this->wallet->id,
                    'amount' => (string) $transaction->amount,
                    'transaction_type' => 'income',
                    'description' => 'Test transaction details',
                ],
            ]);
    }
    
    /**
     * Test that a user can filter transactions by type.
     */
    public function test_user_can_filter_transactions_by_type(): void
    {
        Passport::actingAs($this->user);
        
        // Tạo các giao dịch thu nhập
        WalletTransaction::factory()->count(3)->income()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        // Tạo các giao dịch chi tiêu
        WalletTransaction::factory()->count(2)->expense()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
        ]);
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions/type/income");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        $transactions = $response->json('data.data');
        $this->assertEquals(3, count($transactions));
        foreach ($transactions as $transaction) {
            $this->assertEquals('income', $transaction['transaction_type']);
        }
    }
    
    /**
     * Test that a user can filter transactions by date range.
     */
    public function test_user_can_filter_transactions_by_date_range(): void
    {
        Passport::actingAs($this->user);
        
        // Tạo các giao dịch với ngày khác nhau
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(10),
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(5),
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $this->wallet->id,
            'created_by' => $this->user->id,
            'transaction_date' => now()->subDays(3),
        ]);
        
        $startDate = now()->subDays(7)->startOfDay()->toDateTimeString();
        $endDate = now()->endOfDay()->toDateTimeString();
        
        $response = $this->getJson("/api/wallets/{$this->wallet->id}/transactions/date-range?start_date={$startDate}&end_date={$endDate}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        // Chỉ nên có 2 giao dịch trong khoảng thời gian này (ngày thứ 5 và thứ 3)
        $this->assertEquals(2, count($response->json('data.data')));
    }
    
    /**
     * Test that a user cannot access transactions of another user's wallet.
     */
    public function test_user_cannot_access_transactions_of_another_users_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);
        
        WalletTransaction::factory()->count(3)->create([
            'wallet_id' => $otherWallet->id,
            'created_by' => $otherUser->id,
        ]);
        
        $response = $this->getJson("/api/wallets/{$otherWallet->id}/transactions");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'data' => [],
                    'total' => 0,
                ],
            ]);
    }
    
    /**
     * Test that a user cannot create transaction on another user's wallet.
     */
    public function test_user_cannot_create_transaction_on_another_users_wallet(): void
    {
        Passport::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create([
            'user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);
        
        $category = TransactionCategory::factory()->create([
            'type' => 'income',
        ]);
        
        $transactionData = [
            'wallet_id' => $otherWallet->id,
            'category_id' => $category->id,
            'amount' => 1000,
            'transaction_date' => now()->toDateTimeString(),
            'transaction_type' => 'income',
            'description' => 'Attempted transaction on another wallet',
        ];
        
        $response = $this->postJson('/api/transactions', $transactionData);
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('messages.wallet_transaction.wallet_not_found'),
            ]);
        
        $this->assertDatabaseMissing('wallet_transactions', [
            'wallet_id' => $otherWallet->id,
            'description' => 'Attempted transaction on another wallet',
        ]);
    }
}

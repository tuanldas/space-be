<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\TransactionCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        
        $cashWallet = Wallet::factory()->create([
            'name' => 'Tiền mặt',
            'balance' => 1000000,
            'currency' => 'VND',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        
        $bankWallet = Wallet::factory()->create([
            'name' => 'Tài khoản ngân hàng',
            'balance' => 5000000,
            'currency' => 'VND',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        
        $savingsWallet = Wallet::factory()->create([
            'name' => 'Tiết kiệm',
            'balance' => 10000000,
            'currency' => 'VND',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        
        $foodCategory = TransactionCategory::firstOrCreate(
            ['name' => 'Ăn uống', 'type' => 'expense'],
            ['description' => 'Chi phí ăn uống hàng ngày', 'user_id' => null]
        );
        
        $salaryCategory = TransactionCategory::firstOrCreate(
            ['name' => 'Lương', 'type' => 'income'],
            ['description' => 'Thu nhập từ công việc', 'user_id' => null]
        );
        
        $transferCategory = TransactionCategory::firstOrCreate(
            ['name' => 'Chuyển khoản', 'type' => 'transfer'],
            ['description' => 'Chuyển tiền giữa các ví', 'user_id' => null]
        );
        
        WalletTransaction::factory()->create([
            'wallet_id' => $bankWallet->id,
            'category_id' => $salaryCategory->id,
            'created_by' => $user->id,
            'amount' => 7000000,
            'transaction_date' => now()->subDays(15),
            'transaction_type' => 'income',
            'description' => 'Lương tháng này',
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $cashWallet->id,
            'category_id' => $foodCategory->id,
            'created_by' => $user->id,
            'amount' => 150000,
            'transaction_date' => now()->subDays(5),
            'transaction_type' => 'expense',
            'description' => 'Ăn trưa với đồng nghiệp',
        ]);
        
        WalletTransaction::factory()->create([
            'wallet_id' => $bankWallet->id,
            'category_id' => $transferCategory->id,
            'created_by' => $user->id,
            'amount' => 2000000,
            'transaction_date' => now()->subDays(2),
            'transaction_type' => 'transfer',
            'description' => 'Chuyển tiền vào ví tiết kiệm',
        ]);
        
        WalletTransaction::factory()->count(5)->forWallet($cashWallet)->create();
        WalletTransaction::factory()->count(7)->forWallet($bankWallet)->create();
        WalletTransaction::factory()->count(3)->forWallet($savingsWallet)->create();
    }
}

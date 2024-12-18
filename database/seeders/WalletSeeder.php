<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', SeederConstants::USER_EMAIL)->first();
        if ($user) {
            Wallet::factory()
                ->count(10)
                ->create([
                    'created_by' => $user->id
                ]);
        } else {
            Wallet::factory()
                ->count(10)
                ->create();
        }
    }
}

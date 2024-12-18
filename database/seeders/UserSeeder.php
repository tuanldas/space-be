<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', SeederConstants::USER_EMAIL)->exists()) {
            User::factory()
                ->state([
                    'password' => SeederConstants::USER_PASSWORD
                ])
                ->create([
                    'email' => SeederConstants::USER_EMAIL
                ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@tuanldas.me')->exists()) {
            User::factory()
                ->state([
                    'password' => '123123'
                ])
                ->create([
                    'email' => 'admin@tuanldas.me'
                ]);
        }
    }
}

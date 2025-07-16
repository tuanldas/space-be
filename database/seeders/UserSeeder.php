<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'tuanldas@gmail.com',
            'password' => bcrypt('123123'),
        ]);

        // Create a regular user with predefined credentials
        User::factory()->regularUser()->create([
            'password' => bcrypt('user123'),
        ]);

        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
} 
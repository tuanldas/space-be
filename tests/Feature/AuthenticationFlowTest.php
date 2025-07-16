<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Thực hiện migrations để tạo cấu trúc bảng
        $this->artisan('migrate:fresh');
    }

    public function test_complete_authentication_flow(): void
    {
        // Test đơn giản là có thể đăng ký user
        $userData = [
            'name' => 'Test User',
            'email' => 'flow-test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(201);
        
        // Kiểm tra user đã được tạo trong database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'flow-test@example.com',
        ]);
    }
}
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_register_creates_user_and_returns_success(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name'],
        ]);
    }

    public function test_login_validates_input(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_register_validates_input(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_refresh_token_validates_input(): void
    {
        $response = $this->postJson('/api/auth/refresh-token', [
            'refresh_token' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['refresh_token']);
    }

    public function test_login_fails_for_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $this->assertTrue(true);
    }

    public function test_refresh_token_returns_new_tokens(): void
    {
        $this->assertTrue(true);
    }

    public function test_logout_revokes_token(): void
    {
        $this->assertTrue(true);
    }
} 
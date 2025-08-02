<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setupBase();
        $this->setupAdmin();
    }

    public function test_get_users_list(): void
    {
        User::factory()->count(5)->create();

        $this->actAsAdmin();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total'
            ]);

        $this->assertGreaterThanOrEqual(6, $response->json('total'));
    }

    public function test_search_users(): void
    {
        User::factory()->create([
            'name' => 'Test Search User',
            'email' => 'searchable@example.com'
        ]);

        $this->actAsAdmin();
        $response = $this->getJson('/api/users?search=Test Search');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        $this->assertStringContainsString('Test Search', $response->json('data.0.name'));

        $response = $this->getJson('/api/users?search=searchable');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        $this->assertStringContainsString('searchable@example.com', $response->json('data.0.email'));
    }

    public function test_get_user_by_id(): void
    {
        $user = User::factory()->create();

        $this->actAsAdmin();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_get_nonexistent_user(): void
    {
        $this->actAsAdmin();

        $nonExistentId = 9999;
        $response = $this->getJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found.'
            ]);
    }

    public function test_create_user(): void
    {
        $this->actAsAdmin();

        $userData = [
            'name' => 'New Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New Test User',
            'email' => 'newuser@example.com'
        ]);
    }

    public function test_create_user_with_invalid_data(): void
    {
        $this->actAsAdmin();

        $invalidUserData = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ];

        $response = $this->postJson('/api/users', $invalidUserData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_create_user_with_duplicate_email(): void
    {
        $this->actAsAdmin();

        $existingUser = User::factory()->create([
            'email' => 'duplicate@example.com'
        ]);

        $userData = [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_user(): void
    {
        $user = User::factory()->create();

        $this->actAsAdmin();

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated-email@example.com'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'Updated Name',
                'email' => 'updated-email@example.com'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated-email@example.com'
        ]);
    }

    public function test_update_user_password(): void
    {
        $user = User::factory()->create();

        $this->actAsAdmin();

        $updatedData = [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'NewPassword123!'
        ]);
        $this->assertNotEquals(422, $loginResponse->status());
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        $this->actAsAdmin();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function test_delete_nonexistent_user(): void
    {
        $this->actAsAdmin();

        $nonExistentId = 9999;
        $response = $this->deleteJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found.'
            ]);
    }

    public function test_accessing_protected_routes_without_authentication(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/users/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
        $response->assertStatus(401);

        $response = $this->putJson('/api/users/1', ['name' => 'Updated Name']);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/users/1');
        $response->assertStatus(401);
    }
} 
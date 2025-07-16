<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('migrate:fresh');
        
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);
        \Bouncer::role()->firstOrCreate(['name' => 'admin'], ['title' => 'Quản trị viên']);
        \Bouncer::allow('admin')->everything();
        \Bouncer::assign('admin')->to($this->admin);
        \Bouncer::refresh();
    }

    /**
     * Test lấy danh sách người dùng.
     */
    public function test_get_users_list(): void
    {
        User::factory()->count(5)->create();

        Passport::actingAs($this->admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total'
            ]);

        $this->assertEquals(6, $response->json('total'));
    }

    /**
     * Test tìm kiếm người dùng.
     */
    public function test_search_users(): void
    {
        User::factory()->create([
            'name' => 'Test Search User',
            'email' => 'searchable@example.com'
        ]);

        Passport::actingAs($this->admin);
        $response = $this->getJson('/api/users?search=Test Search');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        $this->assertStringContainsString('Test Search', $response->json('data.0.name'));

        $response = $this->getJson('/api/users?search=searchable');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        $this->assertStringContainsString('searchable@example.com', $response->json('data.0.email'));
    }

    /**
     * Test lấy thông tin người dùng theo ID.
     */
    public function test_get_user_by_id(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($this->admin);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * Test lấy thông tin người dùng không tồn tại.
     */
    public function test_get_nonexistent_user(): void
    {
        Passport::actingAs($this->admin);

        $nonExistentId = 9999;
        $response = $this->getJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found'
            ]);
    }

    /**
     * Test tạo người dùng mới.
     */
    public function test_create_user(): void
    {
        Passport::actingAs($this->admin);

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

    /**
     * Test tạo người dùng với dữ liệu không hợp lệ.
     */
    public function test_create_user_with_invalid_data(): void
    {
        Passport::actingAs($this->admin);

        $invalidUserData = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ];

        $response = $this->postJson('/api/users', $invalidUserData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test tạo người dùng với email đã tồn tại.
     */
    public function test_create_user_with_duplicate_email(): void
    {
        Passport::actingAs($this->admin);

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

    /**
     * Test cập nhật thông tin người dùng.
     */
    public function test_update_user(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($this->admin);

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

    /**
     * Test cập nhật mật khẩu.
     */
    public function test_update_user_password(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($this->admin);

        $updatedData = [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        $response->assertStatus(200);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'NewPassword123!'
        ]);
        $this->assertNotEquals(422, $loginResponse->status());
    }

    /**
     * Test xóa người dùng.
     */
    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($this->admin);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /**
     * Test xóa người dùng không tồn tại.
     */
    public function test_delete_nonexistent_user(): void
    {
        Passport::actingAs($this->admin);

        $nonExistentId = 9999;
        $response = $this->deleteJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'User not found'
            ]);
    }

    /**
     * Test bảo mật API - Truy cập không có token.
     */
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
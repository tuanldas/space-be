<?php

namespace Tests\Feature\Api;

use App\Models\TransactionCategory;
use App\Models\User;
use Bouncer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('migrate:fresh');
        
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'Normal User'
        ]);

        Bouncer::role()->firstOrCreate(['name' => 'admin'], ['title' => 'Quản trị viên']);
        Bouncer::role()->firstOrCreate(['name' => 'user'], ['title' => 'Người dùng']);
        
        Bouncer::allow('admin')->everything();
        Bouncer::assign('admin')->to($this->admin);
        Bouncer::assign('user')->to($this->user);
        Bouncer::refresh();
    }

    /**
     * Test lấy danh sách danh mục giao dịch.
     */
    public function test_get_categories_list(): void
    {
        // Tạo các danh mục giao dịch
        TransactionCategory::factory()
            ->count(3)
            ->forUser()
            ->create(['user_id' => $this->user->id]);
        
        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->getJson('/api/transaction-categories');

        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total'
            ]);
        
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test lọc danh mục theo loại.
     */
    public function test_filter_categories_by_type(): void
    {
        // Tạo các danh mục giao dịch
        TransactionCategory::factory()
            ->count(2)
            ->forUser()
            ->expense()
            ->create(['user_id' => $this->user->id]);
        
        TransactionCategory::factory()
            ->count(3)
            ->forUser()
            ->income()
            ->create(['user_id' => $this->user->id]);
        
        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API với bộ lọc expense
        $responseExpense = $this->getJson('/api/transaction-categories?type=expense');
        $responseExpense->assertStatus(200);
        $this->assertEquals(2, $responseExpense->json('total'));

        // Gọi API với bộ lọc income
        $responseIncome = $this->getJson('/api/transaction-categories?type=income');
        $responseIncome->assertStatus(200);
        $this->assertEquals(3, $responseIncome->json('total'));
    }

    /**
     * Test lấy thông tin danh mục theo ID.
     */
    public function test_get_category_by_id(): void
    {
        // Tạo danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->getJson("/api/transaction-categories/{$category->id}");

        // Kiểm tra
        $response->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ]);
    }

    /**
     * Test tạo danh mục mới.
     */
    public function test_create_category(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Chuẩn bị dữ liệu
        $categoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'type' => 'expense',
        ];

        // Gọi API
        $response = $this->postJson('/api/transaction-categories', $categoryData);

        // Kiểm tra
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id',
                'created_at',
                'updated_at'
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('transaction_categories', [
            'name' => $categoryData['name'],
            'type' => $categoryData['type'],
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Test cập nhật danh mục.
     */
    public function test_update_category(): void
    {
        // Tạo danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Chuẩn bị dữ liệu cập nhật
        $updatedData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated description'
        ];

        // Gọi API
        $response = $this->putJson("/api/transaction-categories/{$category->id}", $updatedData);

        // Kiểm tra
        $response->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => 'Updated Category Name',
                'description' => 'Updated description'
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'description' => 'Updated description'
        ]);
    }

    /**
     * Test xoá danh mục.
     */
    public function test_delete_category(): void
    {
        // Tạo danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->deleteJson("/api/transaction-categories/{$category->id}");

        // Kiểm tra
        $response->assertStatus(204);

        // Kiểm tra soft delete trong database
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test xem danh sách danh mục đã xóa.
     */
    public function test_view_trashed_categories(): void
    {
        // Tạo và xóa danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);
        
        $category->delete();

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->getJson('/api/transaction-categories/trashed');

        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test khôi phục danh mục đã xóa.
     */
    public function test_restore_category(): void
    {
        // Tạo và xóa danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);
        
        $category->delete();

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->postJson("/api/transaction-categories/{$category->id}/restore");

        // Kiểm tra
        $response->assertStatus(200);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Test xóa vĩnh viễn danh mục.
     */
    public function test_force_delete_category(): void
    {
        // Tạo và xóa danh mục
        $category = TransactionCategory::factory()
            ->forUser()
            ->create(['user_id' => $this->user->id]);
        
        $category->delete();

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Gọi API
        $response = $this->deleteJson("/api/transaction-categories/{$category->id}/force");

        // Kiểm tra
        $response->assertStatus(204);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $category->id
        ]);
    }
} 
<?php

namespace Tests\Feature\Api;

use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected User $user;
    protected TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo user mẫu
        $this->user = User::factory()->create();
        
        // Tạo danh mục giao dịch mẫu
        $this->category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'expense'
        ]);
    }

    public function test_get_categories_list(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Tạo thêm vài danh mục cho user đó
        TransactionCategory::factory()->count(5)->create([
            'user_id' => $this->user->id
        ]);
        
        // Gọi API
        $response = $this->getJson('/api/transaction-categories');
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'user_id',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'total'
            ]);
        
        // Kiểm tra số lượng items được trả về (6 = 1 từ setup + 5 mới tạo)
        $this->assertEquals(6, $response->json('total'));
    }

    public function test_filter_categories_by_type(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Tạo thêm các danh mục với type khác nhau
        TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);
        
        // Gọi API với filter type=income
        $response = $this->getJson('/api/transaction-categories?type=income');
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'total'
            ]);
        
        // Kiểm tra chỉ có 2 items loại income
        $this->assertEquals(2, $response->json('total'));
        
        // Kiểm tra tất cả items có type là income
        foreach ($response->json('data') as $item) {
            $this->assertEquals('income', $item['type']);
        }
    }

    public function test_get_category_by_id(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Gọi API
        $response = $this->getJson('/api/transaction-categories/' . $this->category->id);
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id',
                'created_at',
                'updated_at'
            ])
            ->assertJson([
                'id' => $this->category->id,
                'name' => 'Test Category',
                'description' => 'Test Description',
                'type' => 'expense',
                'user_id' => $this->user->id
            ]);
    }

    public function test_create_category(): void
    {
        // Setup fake storage
        Storage::fake('public');

        // Đăng nhập với user
        Passport::actingAs($this->user);

        // Chuẩn bị dữ liệu
        $categoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'type' => 'expense',
            'image' => UploadedFile::fake()->image('category.jpg')
        ];

        // Gọi API
        $response = $this->postJson('/api/transaction-categories', $categoryData);

        // Kiểm tra
        $response->assertStatus(201);

        // Kiểm tra dữ liệu được lưu trong database
        $categoryId = $response->json('id');
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $categoryId,
            'name' => $categoryData['name'],
            'description' => $categoryData['description'],
            'type' => $categoryData['type'],
            'user_id' => $this->user->id
        ]);

        // Kiểm tra file được lưu trữ
        $this->assertDatabaseHas('images', [
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId
        ]);

        // Kiểm tra ảnh có trong response
        $this->assertArrayHasKey('image', $response->json());
    }

    public function test_update_category(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Chuẩn bị dữ liệu cập nhật
        $updatedData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ];
        
        // Gọi API
        $response = $this->putJson('/api/transaction-categories/' . $this->category->id, $updatedData);
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id',
                'created_at',
                'updated_at'
            ])
            ->assertJson([
                'id' => $this->category->id,
                'name' => 'Updated Name',
                'description' => 'Updated Description'
            ]);
        
        // Kiểm tra dữ liệu được cập nhật trong database
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_delete_category(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Gọi API
        $response = $this->deleteJson('/api/transaction-categories/' . $this->category->id);
        
        // Kiểm tra
        $response->assertStatus(204);
        
        // Kiểm tra danh mục đã bị soft delete
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $this->category->id
        ]);
    }

    public function test_view_trashed_categories(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Soft delete một danh mục
        $this->category->delete();
        
        // Tạo thêm một danh mục khác (không bị xoá)
        TransactionCategory::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        // Gọi API
        $response = $this->getJson('/api/transaction-categories/trashed');
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'deleted_at'
                    ]
                ],
                'total'
            ]);
        
        // Kiểm tra chỉ có 1 item trong thùng rác
        $this->assertEquals(1, $response->json('total'));
        
        // Kiểm tra item trong thùng rác có id trùng với danh mục đã xoá
        $this->assertEquals($this->category->id, $response->json('data.0.id'));
    }

    public function test_restore_category(): void
    {
        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Soft delete một danh mục
        $this->category->delete();
        
        // Kiểm tra danh mục đã bị soft delete
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $this->category->id
        ]);
        
        // Gọi API để khôi phục
        $response = $this->postJson('/api/transaction-categories/' . $this->category->id . '/restore');
        
        // Kiểm tra
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id',
                'created_at',
                'updated_at'
            ])
            ->assertJsonMissing([
                'deleted_at'
            ]);
        
        // Kiểm tra danh mục đã được khôi phục
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'deleted_at' => null
        ]);
    }

    public function test_force_delete_category(): void
    {
        // Setup fake storage
        Storage::fake('public');

        // Đăng nhập với user
        Passport::actingAs($this->user);
        
        // Tạo một category mới với hình ảnh
        $image = UploadedFile::fake()->image('category.jpg');
        $response = $this->postJson('/api/transaction-categories', [
            'name' => 'Category To Delete',
            'description' => 'Will be deleted',
            'type' => 'expense',
            'image' => $image
        ]);
        
        $categoryId = $response->json('id');
        
        // Soft delete category
        $category = TransactionCategory::find($categoryId);
        $category->delete();
        
        // Gọi API force delete
        $forceDeleteResponse = $this->deleteJson("/api/transaction-categories/{$categoryId}/force");
        
        // Kiểm tra
        $forceDeleteResponse->assertStatus(204);
        
        // Kiểm tra dữ liệu trong database (đã xóa vĩnh viễn)
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $categoryId
        ]);
        
        // Kiểm tra ảnh cũng đã bị xóa
        $this->assertDatabaseMissing('images', [
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId
        ]);
    }
} 
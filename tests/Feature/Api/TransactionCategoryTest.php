<?php

namespace Tests\Feature\Api;

use App\Enums\AbilityType;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setupBase();
        
        $this->user = $this->createUserWithAbilities([
            AbilityType::VIEW_TRANSACTION_CATEGORIES->value,
            AbilityType::CREATE_TRANSACTION_CATEGORIES->value,
            AbilityType::UPDATE_TRANSACTION_CATEGORIES->value,
            AbilityType::DELETE_TRANSACTION_CATEGORIES->value,
            AbilityType::RESTORE_TRANSACTION_CATEGORIES->value,
            AbilityType::FORCE_DELETE_TRANSACTION_CATEGORIES->value,
        ]);
    }

    public function test_get_categories_list(): void
    {
        $this->actAsUser($this->user);
        
        $response = $this->getJson('/api/transaction-categories');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'is_default'
                    ]
                ],
                'current_page',
                'per_page',
                'total'
            ]);
    }

    public function test_filter_categories_by_type(): void
    {
        $this->actAsUser($this->user);
        
        // Tạo category với type expense
        TransactionCategory::factory()->create(['type' => 'expense']);
        
        $response = $this->getJson('/api/transaction-categories?type=expense');
        
        $response->assertStatus(200)
            ->assertJsonPath('data.0.type', 'expense');
    }

    public function test_get_category_by_id(): void
    {
        $this->actAsUser($this->user);
        
        $category = TransactionCategory::factory()->create();
        
        $response = $this->getJson("/api/transaction-categories/{$category->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'is_default'
            ])
            ->assertJsonPath('id', $category->id);
    }

    public function test_create_category(): void
    {
        Storage::fake('public');
        
        $this->actAsUser($this->user);
        
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'income',
            'image' => UploadedFile::fake()->image('category.jpg')
        ];
        
        $response = $this->postJson('/api/transaction-categories', $categoryData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'is_default',
                'image'
            ])
            ->assertJsonPath('name', 'Test Category')
            ->assertJsonPath('description', 'Test Description')
            ->assertJsonPath('type', 'income');
        
        $this->assertDatabaseHas('transaction_categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'income'
        ]);
    }

    public function test_update_category(): void
    {
        $this->actAsUser($this->user);
        
        $category = TransactionCategory::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'type' => 'expense'
        ]);
        
        $updatedData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'type' => 'income'
        ];
        
        $response = $this->putJson("/api/transaction-categories/{$category->id}", $updatedData);
        
        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('description', 'Updated Description')
            ->assertJsonPath('type', 'income');
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'type' => 'income'
        ]);
    }

    public function test_delete_category(): void
    {
        $this->actAsUser($this->user);
        
        $category = TransactionCategory::factory()->create();
        
        $response = $this->deleteJson("/api/transaction-categories/{$category->id}");
        
        $response->assertStatus(204);
        
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $category->id
        ]);
    }

    public function test_view_trashed_categories(): void
    {
        $this->actAsUser($this->user);
        
        // Tạo category với user_id của người dùng hiện tại
        $category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id
        ]);
        $categoryId = $category->id;
        
        // Xóa tạm category
        $category->delete();
        
        $response = $this->getJson('/api/transaction-categories/trashed');
        
        $response->assertStatus(200);
        
        // Kiểm tra danh sách trả về có category đã xóa không
        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData, 'Danh sách category đã xóa không được trả về');
        
        // Tìm category trong kết quả trả về
        $found = false;
        foreach ($responseData as $item) {
            if ($item['id'] === $categoryId) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found, "Category đã xóa không tìm thấy trong danh sách");
    }

    public function test_restore_category(): void
    {
        $this->actAsUser($this->user);
        
        $category = TransactionCategory::factory()->create();
        $category->delete();
        
        $response = $this->postJson("/api/transaction-categories/{$category->id}/restore");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'deleted_at' => null
        ]);
    }

    public function test_force_delete_category(): void
    {
        $this->actAsUser($this->user);
        
        $category = TransactionCategory::factory()->create();
        $category->delete();
        
        $response = $this->deleteJson("/api/transaction-categories/{$category->id}/force");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $category->id
        ]);
    }
} 
<?php

namespace Tests\Feature\Api;

use App\Enums\AbilityType;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected User $user;
    protected TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed();
        
        $this->user = User::factory()->create();
        
        Bouncer::allow($this->user)->to(AbilityType::VIEW_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::CREATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::UPDATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::DELETE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::RESTORE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::FORCE_DELETE_TRANSACTION_CATEGORIES->value);
        
        $this->category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'expense'
        ]);
        
        Bouncer::refresh();
    }

    public function test_get_categories_list(): void
    {
        Passport::actingAs($this->user);
        
        TransactionCategory::factory()->count(5)->create([
            'user_id' => $this->user->id
        ]);
        
        $response = $this->getJson('/api/transaction-categories');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'user_id'
                    ]
                ],
                'total'
            ]);
        
        $this->assertGreaterThanOrEqual(6, $response->json('total'));
    }

    public function test_filter_categories_by_type(): void
    {
        Passport::actingAs($this->user);
        
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
        
        $response = $this->getJson('/api/transaction-categories?type=income');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'total'
            ]);
        
        $this->assertGreaterThanOrEqual(2, $response->json('total'));
        
        foreach ($response->json('data') as $item) {
            $this->assertEquals('income', $item['type']);
        }
    }

    public function test_get_category_by_id(): void
    {
        Passport::actingAs($this->user);
        
        $response = $this->getJson('/api/transaction-categories/' . $this->category->id);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id'
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
        Storage::fake('public');

        Passport::actingAs($this->user);

        $categoryData = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'type' => 'expense',
            'image' => UploadedFile::fake()->image('category.jpg')
        ];

        $response = $this->postJson('/api/transaction-categories', $categoryData);

        $response->assertStatus(201);

        $categoryId = $response->json('id');
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $categoryId,
            'name' => $categoryData['name'],
            'description' => $categoryData['description'],
            'type' => $categoryData['type'],
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('images', [
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId
        ]);

        $this->assertArrayHasKey('image', $response->json());
    }

    public function test_update_category(): void
    {
        Passport::actingAs($this->user);
        
        $updatedData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ];
        
        $response = $this->putJson('/api/transaction-categories/' . $this->category->id, $updatedData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id'
            ])
            ->assertJson([
                'id' => $this->category->id,
                'name' => 'Updated Name',
                'description' => 'Updated Description'
            ]);
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_delete_category(): void
    {
        Passport::actingAs($this->user);
        
        $response = $this->deleteJson('/api/transaction-categories/' . $this->category->id);
        
        $response->assertStatus(204);
        
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $this->category->id
        ]);
    }

    public function test_view_trashed_categories(): void
    {
        Passport::actingAs($this->user);
        
        $this->category->delete();
        
        TransactionCategory::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $response = $this->getJson('/api/transaction-categories/trashed');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'user_id'
                    ]
                ],
                'total'
            ]);
        
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
        
        $foundDeleted = false;
        foreach ($response->json('data') as $item) {
            if ($item['id'] === $this->category->id) {
                $foundDeleted = true;
                break;
            }
        }
        $this->assertTrue($foundDeleted, 'Deleted category not found in trashed list');
    }

    public function test_restore_category(): void
    {
        Passport::actingAs($this->user);
        
        $this->category->delete();
        
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $this->category->id
        ]);
        
        $response = $this->postJson('/api/transaction-categories/' . $this->category->id . '/restore');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'type',
                'user_id'
            ])
            ->assertJsonMissing([
                'deleted_at'
            ]);
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'deleted_at' => null
        ]);
    }

    public function test_force_delete_category(): void
    {
        Storage::fake('public');

        Passport::actingAs($this->user);
        
        $image = UploadedFile::fake()->image('category.jpg');
        $response = $this->postJson('/api/transaction-categories', [
            'name' => 'Category To Delete',
            'description' => 'Will be deleted',
            'type' => 'expense',
            'image' => $image
        ]);
        
        $response->assertStatus(201);
        $categoryId = $response->json('id');
        
        $category = TransactionCategory::find($categoryId);
        $this->assertNotNull($category, 'Category not found after creation');
        $category->delete();
        
        $forceDeleteResponse = $this->deleteJson("/api/transaction-categories/{$categoryId}/force");
        
        $forceDeleteResponse->assertStatus(204);
        
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $categoryId
        ]);
        
        $this->assertDatabaseMissing('images', [
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId
        ]);
    }
} 
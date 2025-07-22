<?php

namespace Tests\Feature\Api;

use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionCategoryImageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user
        $this->user = User::factory()->create();
        
        // Create a category
        $this->category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);
        
        // Setup fake storage
        Storage::fake('public');
        
        // Authenticate user
        Passport::actingAs($this->user);
    }

    public function test_can_attach_image_when_creating_category(): void
    {
        $image = UploadedFile::fake()->image('category.jpg', 100, 100);
        
        $response = $this->postJson('/api/transaction-categories', [
            'name' => 'Category with Image',
            'type' => 'expense',
            'description' => 'Test category with image',
            'image' => $image
        ]);
        
        $response->assertCreated();
        
        // Get the category ID from the response
        $categoryId = $response->json('id');
        
        // Check if the category exists in the database
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $categoryId,
            'name' => 'Category with Image'
        ]);
        
        // Check if the image exists in the database
        $this->assertDatabaseHas('images', [
            'imageable_id' => $categoryId,
            'imageable_type' => TransactionCategory::class
        ]);
            
        // Check the relationship
        $category = TransactionCategory::with('image')->find($categoryId);
        $this->assertNotNull($category->image);
        
        // Check if the file exists in storage
        Storage::disk('public')->assertExists($category->image->path);
    }

    public function test_cannot_create_category_without_image(): void
    {
        $response = $this->postJson('/api/transaction-categories', [
            'name' => 'Category without Image',
            'type' => 'expense',
            'description' => 'Test category without image'
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_can_update_category_with_image(): void
    {
        $image = UploadedFile::fake()->image('updated.jpg', 100, 100);
        
        $response = $this->putJson("/api/transaction-categories/{$this->category->id}", [
            'name' => 'Updated Category',
            'image' => $image
        ]);
        
        $response->assertOk();
        
        // Check if the category was updated in the database
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'name' => 'Updated Category'
        ]);
        
        // Check if the image exists in the database
        $this->assertDatabaseHas('images', [
            'imageable_id' => $this->category->id,
            'imageable_type' => TransactionCategory::class
        ]);
            
        // Check if the file exists in storage
        $category = TransactionCategory::with('image')->find($this->category->id);
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image->path);
    }
}

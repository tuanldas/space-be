<?php

namespace Tests\Feature\Api;

use App\Enums\AbilityType;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class TransactionCategoryImageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed();
        
        $this->user = User::factory()->create();
        
        Bouncer::allow($this->user)->to(AbilityType::CREATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::UPDATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($this->user)->to(AbilityType::VIEW_TRANSACTION_CATEGORIES->value);
        
        $this->category = TransactionCategory::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);
        
        Storage::fake('public');
        
        Passport::actingAs($this->user);
        
        Bouncer::refresh();
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
        
        $categoryId = $response->json('id');
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $categoryId,
            'name' => 'Category with Image'
        ]);
        
        $this->assertDatabaseHas('images', [
            'imageable_id' => $categoryId,
            'imageable_type' => TransactionCategory::class
        ]);
            
        $category = TransactionCategory::with('image')->find($categoryId);
        $this->assertNotNull($category->image);
        
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
        
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->category->id,
            'name' => 'Updated Category'
        ]);
        
        $this->assertDatabaseHas('images', [
            'imageable_id' => $this->category->id,
            'imageable_type' => TransactionCategory::class
        ]);
            
        $category = TransactionCategory::with('image')->find($this->category->id);
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image->path);
    }
}

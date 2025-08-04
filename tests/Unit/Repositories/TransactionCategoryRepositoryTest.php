<?php

namespace Tests\Unit\Repositories;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Repositories\TransactionCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionCategoryRepositoryTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TransactionCategoryRepository();
    }

    public function test_can_create_transaction_category(): void
    {
        $initialCount = TransactionCategory::count();
        
        $data = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'type' => 'expense',
            'user_id' => User::factory()->create()->id
        ];
        
        $result = $this->repository->create($data);
        
        $this->assertInstanceOf(TransactionCategory::class, $result);
        $this->assertEquals($initialCount + 1, TransactionCategory::count());
        $this->assertEquals($data['name'], $result->name);
        $this->assertEquals($data['description'], $result->description);
        $this->assertEquals($data['type'], $result->type);
    }
    
    public function test_can_update_transaction_category(): void
    {
        $category = TransactionCategory::factory()->create();
        
        $newName = $this->faker->word();
        $newDescription = $this->faker->sentence();
        
        $result = $this->repository->updateByUuid(
            $category->id,
            [
                'name' => $newName,
                'description' => $newDescription
            ]
        );
        
        $this->assertTrue($result);
        
        $category->refresh();
        $this->assertEquals($newName, $category->name);
        $this->assertEquals($newDescription, $category->description);
    }
    
    public function test_can_delete_transaction_category(): void
    {
        $category = TransactionCategory::factory()->create();
        $categoryId = $category->id;
        
        $result = $this->repository->deleteByUuid($categoryId);
        
        $this->assertTrue($result);
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $categoryId
        ]);
    }
    
    public function test_can_find_by_uuid(): void
    {
        $category = TransactionCategory::factory()->create();
        
        $result = $this->repository->findByUuid($category->id);
        
        $this->assertInstanceOf(TransactionCategory::class, $result);
        $this->assertEquals($category->id, $result->id);
    }
    
    public function test_can_filter_categories_by_type(): void
    {
        // Count initial categories by type
        $initialExpenseCount = $this->repository->getAllByType('expense')->total();
        $initialIncomeCount = $this->repository->getAllByType('income')->total();
        
        // Create new categories
        TransactionCategory::factory()->count(3)->create(['type' => 'expense']);
        TransactionCategory::factory()->count(2)->create(['type' => 'income']);
        
        // Get categories by type
        $expenseCategories = $this->repository->getAllByType('expense');
        $incomeCategories = $this->repository->getAllByType('income');
        
        // Assert counts
        $this->assertEquals($initialExpenseCount + 3, $expenseCategories->total());
        $this->assertEquals($initialIncomeCount + 2, $incomeCategories->total());
    }
    
    public function test_can_get_default_categories(): void
    {
        // Count initial default categories
        $initialDefaultCount = $this->repository->getAllDefaultCategories()->total();
        
        // Create new default categories
        TransactionCategory::factory()->count(2)->create([
            'user_id' => null,
            'is_default' => true
        ]);
        
        // Get default categories
        $defaultCategories = $this->repository->getAllDefaultCategories();
        
        // Assert count
        $this->assertEquals($initialDefaultCount + 2, $defaultCategories->total());
    }
    
    public function test_can_restore_deleted_category(): void
    {
        $category = TransactionCategory::factory()->create();
        $category->delete();
        
        $result = $this->repository->restore($category->id);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'deleted_at' => null
        ]);
    }
    
    public function test_can_force_delete_category(): void
    {
        $category = TransactionCategory::factory()->create();
        $categoryId = $category->id;
        $category->delete();
        
        $result = $this->repository->forceDelete($categoryId);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $categoryId
        ]);
    }
} 

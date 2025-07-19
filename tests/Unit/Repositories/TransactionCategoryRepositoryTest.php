<?php

namespace Tests\Unit\Repositories;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Repositories\TransactionCategoryRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\Uid\Uuid;

class TransactionCategoryRepositoryTest extends RepositoryTestCase
{
    use WithFaker;

    private TransactionCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TransactionCategoryRepository::class);
    }

    public function test_can_create_transaction_category(): void
    {
        // Arrange
        $user = User::factory()->create();
        $categoryData = [
            'name' => $this->faker->word,
            'type' => 'expense',
            'description' => $this->faker->sentence,
            'user_id' => $user->id,
            'is_default' => false,
        ];

        // Act
        $category = $this->repository->create($categoryData);

        // Assert
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'name' => $categoryData['name'],
            'type' => $categoryData['type'],
            'user_id' => $user->id,
        ]);

        $this->assertIsString($category->id);
        $this->assertTrue(Uuid::isValid($category->id));
    }

    public function test_can_find_transaction_category_by_uuid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => $this->faker->word,
            'type' => 'income',
            'description' => $this->faker->sentence,
            'user_id' => $user->id,
            'is_default' => false,
        ]);

        // Act
        $foundCategory = $this->repository->findByUuid($category->id);

        // Assert
        $this->assertNotNull($foundCategory);
        $this->assertEquals($category->id, $foundCategory->id);
        $this->assertEquals($category->name, $foundCategory->name);
    }

    public function test_can_update_transaction_category_by_uuid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => $this->faker->word,
            'type' => 'expense',
            'description' => $this->faker->sentence,
            'user_id' => $user->id,
            'is_default' => false,
        ]);

        $updatedData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated description',
        ];

        // Act
        $result = $this->repository->updateByUuid($category->id, $updatedData);
        $updatedCategory = $this->repository->findByUuid($category->id);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals('Updated Category Name', $updatedCategory->name);
        $this->assertEquals('Updated description', $updatedCategory->description);
        $this->assertEquals('expense', $updatedCategory->type); // Không thay đổi
    }

    public function test_can_delete_transaction_category_by_uuid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => $this->faker->word,
            'type' => 'expense',
            'description' => $this->faker->sentence,
            'user_id' => $user->id,
            'is_default' => false,
        ]);

        // Act
        $result = $this->repository->deleteByUuid($category->id);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $category->id
        ]);
    }

    public function test_can_paginate_transaction_categories(): void
    {
        // Arrange
        $user = User::factory()->create();
        TransactionCategory::factory()->count(15)->create([
            'user_id' => $user->id
        ]);

        // Act
        $result = $this->repository->paginate(10);

        // Assert
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    public function test_can_filter_categories_by_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        TransactionCategory::create([
            'name' => 'Expense Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        TransactionCategory::create([
            'name' => 'Income Category',
            'type' => 'income',
            'user_id' => $user->id,
        ]);

        // Act
        $expenseCategories = $this->repository->getAllByType('expense');
        $incomeCategories = $this->repository->getAllByType('income');

        // Assert
        $this->assertEquals(1, $expenseCategories->total());
        $this->assertEquals('Expense Category', $expenseCategories->items()[0]->name);
        
        $this->assertEquals(1, $incomeCategories->total());
        $this->assertEquals('Income Category', $incomeCategories->items()[0]->name);
    }

    public function test_can_get_default_categories(): void
    {
        // Arrange
        TransactionCategory::create([
            'name' => 'Default Category',
            'type' => 'expense',
            'is_default' => true,
        ]);
        
        TransactionCategory::create([
            'name' => 'User Category',
            'type' => 'expense',
            'user_id' => User::factory()->create()->id,
            'is_default' => false,
        ]);

        // Act
        $defaultCategories = $this->repository->getAllDefaultCategories();

        // Assert
        $this->assertEquals(1, $defaultCategories->total());
        $this->assertEquals('Default Category', $defaultCategories->items()[0]->name);
        $this->assertTrue($defaultCategories->items()[0]->is_default);
    }

    public function test_can_restore_deleted_category(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => $this->faker->word,
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        $category->delete();
        $this->assertSoftDeleted('transaction_categories', ['id' => $category->id]);

        // Act
        $result = $this->repository->restore($category->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'deleted_at' => null
        ]);
    }

    public function test_can_force_delete_category(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => $this->faker->word,
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        $category->delete();

        // Act
        $result = $this->repository->forceDelete($category->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $category->id
        ]);
    }
} 
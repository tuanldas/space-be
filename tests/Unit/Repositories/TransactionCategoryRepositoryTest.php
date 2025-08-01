<?php

namespace Tests\Unit\Repositories;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Repositories\TransactionCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TransactionCategoryRepository();
    }

    public function test_can_create_transaction_category(): void
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'Test Category',
            'type' => 'expense',
            'icon' => 'shopping-bag',
            'user_id' => $user->id,
        ];

        $category = $this->repository->create($data);

        $this->assertInstanceOf(TransactionCategory::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('expense', $category->type);
        $this->assertEquals('shopping-bag', $category->icon);
        $this->assertEquals($user->id, $category->user_id);
    }

    public function test_can_update_transaction_category(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Original Name',
            'type' => 'expense',
            'icon' => 'shopping-bag',
            'user_id' => $user->id,
        ]);

        $data = [
            'name' => 'Updated Name',
            'icon' => 'dollar-sign',
        ];

        $result = $this->repository->update($category->id, $data);

        $this->assertTrue($result);
        $updatedCategory = $this->repository->findById($category->id);
        $this->assertEquals('Updated Name', $updatedCategory->name);
        $this->assertEquals('dollar-sign', $updatedCategory->icon);
        $this->assertEquals('expense', $updatedCategory->type);
        $this->assertEquals($user->id, $updatedCategory->user_id);
    }

    public function test_can_delete_transaction_category(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Test Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);

        $result = $this->repository->deleteById($category->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_find_by_id(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Test Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);

        $result = $this->repository->findById($category->id);

        $this->assertInstanceOf(TransactionCategory::class, $result);
        $this->assertEquals($category->id, $result->id);
        $this->assertEquals('Test Category', $result->name);
    }

    public function test_can_paginate_transaction_categories(): void
    {
        User::factory()->create();
        TransactionCategory::factory()->count(15)->create();

        $result = $this->repository->paginate(10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    public function test_can_filter_categories_by_type(): void
    {
        $user = User::factory()->create();
        
        TransactionCategory::factory()->count(5)->create([
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        TransactionCategory::factory()->count(3)->create([
            'type' => 'income',
            'user_id' => $user->id,
        ]);

        $expenseCategories = $this->repository->getAllByType('expense');
        $incomeCategories = $this->repository->getAllByType('income');

        $this->assertEquals(5, $expenseCategories->total());
        $this->assertEquals(3, $incomeCategories->total());
    }
    
    public function test_can_get_default_categories(): void
    {
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

        $defaultCategories = $this->repository->getAllDefaultCategories();

        $this->assertEquals(1, $defaultCategories->total());
        $this->assertEquals('Default Category', $defaultCategories->items()[0]->name);
        $this->assertTrue($defaultCategories->items()[0]->is_default);
    }

    public function test_can_restore_deleted_category(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Test Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        $category->delete();
        
        $this->assertSoftDeleted('transaction_categories', [
            'id' => $category->id
        ]);

        $result = $this->repository->restore($category->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'deleted_at' => null
        ]);
    }

    public function test_can_get_trashed_categories(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Test Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        $category->delete();

        $trashedCategories = $this->repository->getTrashed();

        $this->assertEquals(1, $trashedCategories->total());
        $this->assertEquals('Test Category', $trashedCategories->items()[0]->name);
    }

    public function test_can_force_delete_category(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::create([
            'name' => 'Test Category',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
        
        $category->delete();

        $result = $this->repository->forceDelete($category->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transaction_categories', [
            'id' => $category->id
        ]);
    }
} 

<?php

namespace Tests\Unit\Services;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Repositories\Interfaces\TransactionCategoryRepositoryInterface;
use App\Services\TransactionCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class TransactionCategoryServiceTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    private $mockRepository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(TransactionCategoryRepositoryInterface::class);
        $this->service = new TransactionCategoryService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_calls_repository_paginate(): void
    {
        // Arrange
        $perPage = 15;
        $expectedPaginator = new LengthAwarePaginator([], 0, $perPage);
        
        $this->mockRepository->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($expectedPaginator);
        
        // Act
        $result = $this->service->getAll($perPage);
        
        // Assert
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_get_by_id_calls_repository_find_by_uuid(): void
    {
        // Arrange
        $uuid = 'fake-uuid';
        $expectedCategory = new TransactionCategory();
        
        $this->mockRepository->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn($expectedCategory);
        
        // Act
        $result = $this->service->getById($uuid);
        
        // Assert
        $this->assertSame($expectedCategory, $result);
    }

    public function test_create_calls_repository_create(): void
    {
        // Arrange
        $categoryData = [
            'name' => $this->faker->word,
            'type' => 'expense',
            'description' => $this->faker->sentence,
        ];
        
        $expectedCategory = new TransactionCategory($categoryData);
        
        $this->mockRepository->shouldReceive('create')
            ->once()
            ->with($categoryData)
            ->andReturn($expectedCategory);
        
        // Act
        $result = $this->service->create($categoryData);
        
        // Assert
        $this->assertSame($expectedCategory, $result);
    }

    public function test_update_calls_repository_update_by_uuid(): void
    {
        // Arrange
        $uuid = 'fake-uuid';
        $categoryData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];
        
        $this->mockRepository->shouldReceive('updateByUuid')
            ->once()
            ->with($uuid, $categoryData)
            ->andReturn(true);
        
        // Act
        $result = $this->service->update($uuid, $categoryData);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_calls_repository_delete_by_uuid(): void
    {
        // Arrange
        $uuid = 'fake-uuid';
        
        $this->mockRepository->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(true);
        
        // Act
        $result = $this->service->delete($uuid);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_get_all_by_type_calls_repository_get_all_by_type(): void
    {
        // Arrange
        $type = 'expense';
        $perPage = 15;
        $expectedPaginator = new LengthAwarePaginator([], 0, $perPage);
        
        $this->mockRepository->shouldReceive('getAllByType')
            ->once()
            ->with($type, $perPage)
            ->andReturn($expectedPaginator);
        
        // Act
        $result = $this->service->getAllByType($type, $perPage);
        
        // Assert
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_get_all_default_categories_calls_repository_get_all_default_categories(): void
    {
        // Arrange
        $perPage = 15;
        $expectedPaginator = new LengthAwarePaginator([], 0, $perPage);
        
        $this->mockRepository->shouldReceive('getAllDefaultCategories')
            ->once()
            ->with($perPage)
            ->andReturn($expectedPaginator);
        
        // Act
        $result = $this->service->getAllDefaultCategories($perPage);
        
        // Assert
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_get_all_by_user_calls_repository_get_all_by_user(): void
    {
        // Arrange
        $userId = 1;
        $perPage = 15;
        $expectedPaginator = new LengthAwarePaginator([], 0, $perPage);
        
        $this->mockRepository->shouldReceive('getAllByUser')
            ->once()
            ->with($userId, $perPage)
            ->andReturn($expectedPaginator);
        
        // Act
        $result = $this->service->getAllByUser($userId, $perPage);
        
        // Assert
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_restore_calls_repository_restore(): void
    {
        // Arrange
        $uuid = 'fake-uuid';
        
        $this->mockRepository->shouldReceive('restore')
            ->once()
            ->with($uuid)
            ->andReturn(true);
        
        // Act
        $result = $this->service->restore($uuid);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_force_delete_calls_repository_force_delete(): void
    {
        // Arrange
        $uuid = 'fake-uuid';
        
        $this->mockRepository->shouldReceive('forceDelete')
            ->once()
            ->with($uuid)
            ->andReturn(true);
        
        // Act
        $result = $this->service->forceDelete($uuid);
        
        // Assert
        $this->assertTrue($result);
    }
} 
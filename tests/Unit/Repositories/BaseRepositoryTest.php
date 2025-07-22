<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseRepositoryTest extends RepositoryTestCase
{
    private UserRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }
    
    public function test_all_returns_all_records(): void
    {
        // Arrange
        User::factory()->count(3)->create();
        
        // Act
        $result = $this->repository->all();
        
        // Assert
        $this->assertEquals(3, $result->count());
    }
    
    public function test_find_by_id_returns_model_with_correct_id(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $result = $this->repository->findById($user->id);
        
        // Assert
        $this->assertEquals($user->id, $result->id);
    }
    
    public function test_find_by_id_throws_exception_for_nonexistent_id(): void
    {
        // Arrange
        $this->expectException(ModelNotFoundException::class);
        
        // Act
        $this->repository->findById(999);
    }
    
    public function test_create_returns_created_model(): void
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ];
        
        // Act
        $result = $this->repository->create($userData);
        
        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userData['name'], $result->name);
        $this->assertEquals($userData['email'], $result->email);
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }
    
    public function test_update_returns_true_on_success(): void
    {
        // Arrange
        $user = User::factory()->create();
        $newData = ['name' => 'Updated Name'];
        
        // Act
        $result = $this->repository->update($user->id, $newData);
        
        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }
    
    public function test_delete_by_id_returns_true_on_success(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $result = $this->repository->deleteById($user->id);
        
        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
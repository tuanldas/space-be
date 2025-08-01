<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BaseRepositoryTest extends RepositoryTestCase
{
    use RefreshDatabase;

    public function test_all_returns_all_records(): void
    {
        User::factory()->count(3)->create();
        
        $result = $this->repository->all();

        $this->assertEquals(3, $result->count());
    }
    
    public function test_find_by_id_returns_model_with_correct_id(): void
    {
        $user = User::factory()->create();
        
        $result = $this->repository->findById($user->id);
        
        $this->assertEquals($user->id, $result->id);
    }
    
    public function test_find_by_id_returns_null_for_nonexistent_id(): void
    {
        $nonExistentId = 999;
        
        $result = $this->repository->findById($nonExistentId);
        
        $this->assertNull($result);
    }
    
    public function test_create_adds_new_model_to_database(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ];
        
        $result = $this->repository->create($data);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Test User', $result->name);
        $this->assertEquals('test@example.com', $result->email);
        
        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
    
    public function test_update_modifies_existing_model(): void
    {
        $user = User::factory()->create();
        $data = ['name' => 'Updated User'];
        
        $result = $this->repository->update($user->id, $data);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
        ]);
    }
    
    public function test_delete_by_id_returns_true_on_success(): void
    {
        $user = User::factory()->create();
        
        $result = $this->repository->deleteById($user->id);
        
        $this->assertTrue($result);
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
}

<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\WithFaker;

class UserRepositoryTest extends RepositoryTestCase
{
    use WithFaker;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User());
    }

    public function test_can_create_user(): void
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'Password123!'
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $userData['email']
        ]);
    }

    public function test_can_find_user_by_email(): void
    {
        // Arrange
        $email = $this->faker->unique()->safeEmail;
        $user = User::factory()->create(['email' => $email]);

        // Act
        $foundUser = $this->userRepository->findByEmail($email);

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals($email, $foundUser->email);
    }

    public function test_returns_null_for_nonexistent_email(): void
    {
        // Act
        $foundUser = $this->userRepository->findByEmail('nonexistent@example.com');

        // Assert
        $this->assertNull($foundUser);
    }
} 
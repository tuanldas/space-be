<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    /**
     * UserService constructor.
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->getUsersWithRoles($perPage, $filters);
    }

    /**
     * @inheritDoc
     */
    public function getUserById(int $userId): ?User
    {
        $user = $this->userRepository->findById($userId, ['*'], ['roles' => function($query) {
            $query->select(['roles.id', 'roles.name', 'roles.title']);
        }]);
        
        if ($user) {
            $user->roles->makeHidden(['pivot']);
        }
        
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function createUser(array $data): ?User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function updateUser(int $userId, array $data): ?User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            return null;
        }
        
        $user->update($data);
        
        return $user->fresh();
    }

    /**
     * @inheritDoc
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->deleteById($userId);
    }
} 
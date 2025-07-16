<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Get all users with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get user by ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserById(int $userId): ?User;

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User|null
     */
    public function createUser(array $data): ?User;

    /**
     * Update user.
     *
     * @param int $userId
     * @param array $data
     * @return User|null
     */
    public function updateUser(int $userId, array $data): ?User;

    /**
     * Delete user.
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool;
} 
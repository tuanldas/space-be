<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template T of \App\Models\User
 * @extends EloquentRepositoryInterface<T>
 */
interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Find a user by their email address.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get all users with roles and apply filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getUsersWithRoles(int $perPage = 15, array $filters = []): LengthAwarePaginator;
} 
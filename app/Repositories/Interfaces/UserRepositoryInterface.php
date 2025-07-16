<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Find user by email.
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
} 
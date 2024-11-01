<?php

namespace App\Repositories;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $getEmail)
    {
        return User::where('email', $getEmail)->first();
    }
}

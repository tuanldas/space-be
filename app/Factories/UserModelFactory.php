<?php

namespace App\Factories;

use App\Domain\Entities\UserEntity;
use App\Domain\Factories\UserFactory;
use App\Models\User;

class UserModelFactory implements UserFactory
{
    public function make(array $attributes = []): UserEntity
    {
        return new User();
    }
}

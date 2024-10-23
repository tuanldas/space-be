<?php

namespace App\Domain\Factories;

use App\Domain\Entities\UserEntity;

interface UserFactory
{
    public function make(array $attributes = []): UserEntity;
}

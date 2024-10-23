<?php

namespace App\Domain\Entities;

interface UserEntity
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getEmail(): string;

    public function setEmail(string $email): void;

    public function getPassword(): string;

    public function setPassword(string $password): void;
}

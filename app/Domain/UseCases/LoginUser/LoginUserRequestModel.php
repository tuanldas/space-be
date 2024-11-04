<?php

namespace App\Domain\UseCases\LoginUser;

readonly class LoginUserRequestModel
{
    /*
     * @param array{
     *  email: string,
     *  password: string
     * } $attributes
     * */
    public function __construct(
        private array $attributes
    )
    {
    }

    public function getEmail(): string
    {
        return $this->attributes['email'];
    }

    public function getPassword(): string
    {
        return $this->attributes['password'];
    }
}

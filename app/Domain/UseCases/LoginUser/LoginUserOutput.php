<?php

namespace App\Domain\UseCases\LoginUser;

interface LoginUserOutput
{

    public function emailNotFound(string $message);

    public function passwordNotMatch(string $message);

    public function token(array $tokenGenerator);
}

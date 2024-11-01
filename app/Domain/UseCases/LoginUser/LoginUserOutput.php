<?php

namespace App\Domain\UseCases\LoginUser;

interface LoginUserOutput
{

    public function emailNotFound(string $string);
}

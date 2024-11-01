<?php

namespace App\Adapters\Presenters\LoginUser;

use App\Domain\UseCases\LoginUser\LoginUserOutput;

class LoginUserJsonPresenter implements LoginUserOutput
{

    public function emailNotFound(string $string)
    {
        // TODO: Implement emailNotFound() method.
    }
}

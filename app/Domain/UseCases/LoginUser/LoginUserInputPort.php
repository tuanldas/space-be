<?php

namespace App\Domain\UseCases\LoginUser;

interface LoginUserInputPort
{
    public function handle(LoginUserRequestModel $loginUserRequestModel);
}

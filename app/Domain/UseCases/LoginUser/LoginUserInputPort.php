<?php

namespace App\Domain\UseCases\LoginUser;

use App\Adapters\ViewModels\JsonResourceViewModel;

interface LoginUserInputPort
{
    public function handle(LoginUserRequestModel $loginUserRequestModel): JsonResourceViewModel;
}

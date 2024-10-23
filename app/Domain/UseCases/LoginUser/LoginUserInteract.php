<?php

namespace App\Domain\UseCases\LoginUser;

use App\Domain\Factories\UserFactory;
use App\Factories\UserModelFactory;

class LoginUserInteract implements LoginUserInputPort
{
    public function __construct(
        private readonly LoginUserOutput $output,
        private readonly UserFactory     $userFactory,
    )
    {
    }

    public function handle(LoginUserRequestModel $loginUserRequestModel): LoginUserOutput
    {
        $user = $this->userFactory->make([
            'email' => $loginUserRequestModel->getEmail(),
            'password' => $loginUserRequestModel->getPassword(),
        ]);
        return $this->output;
    }
}

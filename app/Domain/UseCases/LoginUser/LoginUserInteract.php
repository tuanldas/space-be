<?php

namespace App\Domain\UseCases\LoginUser;

use App\Adapters\TokenGenerator\TokenGeneratorInterface;
use App\Domain\Factories\UserFactory;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

readonly class LoginUserInteract implements LoginUserInputPort
{
    public function __construct(
        private LoginUserOutput         $output,
        private UserFactory             $userFactory,
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator,
    )
    {
    }

    public function handle(LoginUserRequestModel $loginUserRequestModel)
    {
        $user = $this->userFactory->make([
            'email' => $loginUserRequestModel->getEmail(),
            'password' => $loginUserRequestModel->getPassword(),
        ]);
        $userModel = $this->userRepository->findByEmail($user->getEmail());
        if (!$userModel) {
            $this->output->emailNotFound(__('auth.email'));
        }
        if (!Auth::attempt(['email' => $loginUserRequestModel->getEmail(), 'password' => $loginUserRequestModel->getPassword()])) {
            $this->output->passwordNotMatch(__('auth.failed'));
        }
        $tokenGenerator = $this->tokenGenerator->generate($loginUserRequestModel->getEmail(), $loginUserRequestModel->getPassword());
        return $this->output->token($tokenGenerator);
    }
}

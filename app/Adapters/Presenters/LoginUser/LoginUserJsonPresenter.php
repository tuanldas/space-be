<?php

namespace App\Adapters\Presenters\LoginUser;

use App\Adapters\Presenters\JsonPresenterHelpers;
use App\Adapters\ViewModels\JsonResourceViewModel;
use App\Domain\UseCases\LoginUser\LoginUserOutput;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LoginUserJsonPresenter implements LoginUserOutput
{
    use JsonPresenterHelpers;

    public function emailNotFound(string $message): JsonResourceViewModel
    {
        return $this->notFound($message);
    }

    public function passwordNotMatch(string $message): JsonResourceViewModel
    {
        return $this->notFound($message);
    }

    public function token(array $tokenGenerator): JsonResourceViewModel
    {
        return new JsonResourceViewModel(
            (new JsonResource($tokenGenerator))
                ->additional([
                    'message' => 'Token generated successfully',
                ]),
            ResponseAlias::HTTP_OK
        );
    }
}

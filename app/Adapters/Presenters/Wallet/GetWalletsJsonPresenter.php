<?php

namespace App\Adapters\Presenters\Wallet;

use App\Adapters\ViewModels\JsonResourceViewModel;
use App\Domain\UseCases\Wallets\GetWalletOutput;
use App\Domain\UseCases\Wallets\GetWalletRequestModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetWalletsJsonPresenter implements GetWalletOutput
{
    public function success(GetWalletRequestModel $request)
    {
        return new JsonResourceViewModel(
            new JsonResource([]),
            ResponseAlias::HTTP_OK
        );
    }
}

<?php

namespace App\Adapters\Presenters\Wallet;

use App\Adapters\ViewModels\JsonResourceViewModel;
use App\Domain\UseCases\Wallets\GetWalletOutput;
use App\Http\Resources\WalletCollection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetWalletsJsonPresenter implements GetWalletOutput
{
    public function success($request)
    {
        return new JsonResourceViewModel(
            new WalletCollection($request),
            ResponseAlias::HTTP_OK
        );
    }
}

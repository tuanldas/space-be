<?php

namespace App\Domain\UseCases\Wallets;

use App\Adapters\ViewModels\JsonResourceViewModel;

interface GetWalletInputPort
{
    public function handle(GetWalletRequestModel $request): JsonResourceViewModel;
}

<?php

namespace App\Domain\UseCases\Wallets;

interface GetWalletOutput
{

    public function success(GetWalletRequestModel $request);
}

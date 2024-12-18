<?php

namespace App\Http\Controllers\Api;

use App\Domain\UseCases\Wallets\GetWalletInputPort;
use App\Domain\UseCases\Wallets\GetWalletRequestModel;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function __construct(
        private readonly GetWalletInputPort $getWalletInputPort
    )
    {
    }

    public function index()
    {
        $response = $this->getWalletInputPort->handle(new GetWalletRequestModel());
        return $response->getResource()->response();
    }
}

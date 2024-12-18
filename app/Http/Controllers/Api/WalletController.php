<?php

namespace App\Http\Controllers\Api;

use App\Domain\UseCases\Wallets\GetWalletInputPort;
use App\Domain\UseCases\Wallets\GetWalletRequestModel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        private readonly GetWalletInputPort $getWalletInputPort
    )
    {
    }

    public function index()
    {
        $response = $this->getWalletInputPort->handle(new GetWalletRequestModel(['userId' => Auth::user()->id]));
        return $response->getResource()->response();
    }
}

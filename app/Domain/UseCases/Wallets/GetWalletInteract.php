<?php

namespace App\Domain\UseCases\Wallets;

use App\Adapters\ViewModels\JsonResourceViewModel;
use App\Domain\Repositories\WalletRepositoryInterface;

class GetWalletInteract implements GetWalletInputPort
{
    public function __construct(
        private GetWalletOutput           $output,
        private WalletRepositoryInterface $walletRepository
    )
    {
    }

    public function handle(GetWalletRequestModel $request): JsonResourceViewModel
    {
        $wallets = $this->walletRepository->getWallets($request->getUserId());
        return $this->output->success($wallets);
    }
}

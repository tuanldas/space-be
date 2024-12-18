<?php

namespace App\Domain\UseCases\Wallets;

use App\Adapters\ViewModels\JsonResourceViewModel;

class GetWalletInteract implements GetWalletInputPort
{
    public function __construct(
        private GetWalletOutput $output,
    )
    {
    }

    public function handle(GetWalletRequestModel $request): JsonResourceViewModel
    {

        return $this->output->success($request);
    }
}

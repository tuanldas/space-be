<?php

namespace App\Repositories;

use App\Domain\Repositories\WalletRepositoryInterface;
use App\Models\Wallet;

class WalletRepository extends BaseRepository implements WalletRepositoryInterface
{
    public function getModel()
    {
        return Wallet::class;
    }

    public function getWallets(string $userId)
    {
        return $this->model
            ->where('created_by', $userId)
            ->paginate(10);
    }
}

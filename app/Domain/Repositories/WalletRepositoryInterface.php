<?php

namespace App\Domain\Repositories;

interface WalletRepositoryInterface
{
    public function getWallets(string $userId);
}

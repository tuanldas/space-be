<?php

namespace App\Repositories\Interfaces;

interface WalletTransactionRepositoryInterface extends EloquentRepositoryInterface
{
    public function getTransactionsByWalletId(string $walletId, array $columns = ['*']);

    public function getTransactions(
        string $walletId,
        array $columns = ['*']
    );
} 
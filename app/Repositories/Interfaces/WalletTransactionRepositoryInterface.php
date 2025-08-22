<?php

namespace App\Repositories\Interfaces;

interface WalletTransactionRepositoryInterface extends EloquentRepositoryInterface
{
    public function getTransactionsByWalletId(string $walletId, array $columns = ['*']);

    public function getTransactions(
        string $walletId,
        array $columns = ['*']
    );

    /**
     * Lấy danh sách giao dịch của người dùng theo bộ lọc
     *
     * @param int $userId ID người dùng
     * @param array $columns Các cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserTransactions(int $userId, array $columns = ['*']);
} 
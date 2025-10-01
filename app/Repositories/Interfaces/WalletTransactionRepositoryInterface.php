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

    /**
     * Lấy tổng chi tiêu theo khoảng thời gian
     *
     * @param int $userId ID người dùng
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @param string|null $walletId ID ví (optional)
     * @return array ['total' => float, 'count' => int]
     */
    public function getExpensesByDateRange(int $userId, string $startDate, string $endDate, ?string $walletId = null): array;
} 
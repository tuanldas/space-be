<?php

namespace App\Repositories\Interfaces;

interface WalletTransactionRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Lấy danh sách giao dịch của ví
     *
     * @param string $walletId ID của ví
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByWalletId(string $walletId, array $columns = ['*']);

    /**
     * Lấy danh sách giao dịch theo loại
     *
     * @param string $walletId ID của ví
     * @param string $type Loại giao dịch
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByType(string $walletId, string $type, array $columns = ['*']);

    /**
     * Lấy danh sách giao dịch trong khoảng thời gian
     *
     * @param string $walletId ID của ví
     * @param string $startDate Ngày bắt đầu
     * @param string $endDate Ngày kết thúc
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByDateRange(
        string $walletId, 
        string $startDate, 
        string $endDate, 
        array $columns = ['*']
    );
} 
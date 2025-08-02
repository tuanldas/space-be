<?php

namespace App\Services\Interfaces;

interface WalletTransactionServiceInterface
{
    /**
     * Lấy danh sách giao dịch của ví
     *
     * @param string $walletId ID của ví
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByWalletId(string $walletId);

    /**
     * Lấy thông tin chi tiết của giao dịch
     *
     * @param string $id ID của giao dịch
     * @return \App\Models\WalletTransaction|null
     */
    public function getTransactionById(string $id);

    /**
     * Tạo giao dịch mới
     *
     * @param array $data Dữ liệu giao dịch
     * @return \App\Models\WalletTransaction|null
     */
    public function createTransaction(array $data);

    /**
     * Xóa giao dịch
     *
     * @param string $id ID của giao dịch
     * @return bool
     */
    public function deleteTransaction(string $id);

    /**
     * Lấy danh sách giao dịch theo loại
     *
     * @param string $walletId ID của ví
     * @param string $type Loại giao dịch
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByType(string $walletId, string $type);

    /**
     * Lấy danh sách giao dịch trong khoảng thời gian
     *
     * @param string $walletId ID của ví
     * @param string $startDate Ngày bắt đầu
     * @param string $endDate Ngày kết thúc
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByDateRange(
        string $walletId,
        string $startDate,
        string $endDate
    );
} 
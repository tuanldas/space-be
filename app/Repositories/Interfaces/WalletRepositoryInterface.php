<?php

namespace App\Repositories\Interfaces;

interface WalletRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Lấy danh sách ví của người dùng
     *
     * @param int $userId ID của người dùng
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWalletsByUserId(int $userId, array $columns = ['*']);

    /**
     * Cập nhật số dư ví
     *
     * @param string $id ID của ví
     * @param float $amount Số tiền cần cập nhật (dương: tăng, âm: giảm)
     * @return bool
     */
    public function updateBalance(string $id, float $amount);
    
    /**
     * Lấy thông tin tóm tắt của các ví cho sidebar
     *
     * @param int $userId ID của người dùng
     * @return \Illuminate\Support\Collection
     */
    public function getWalletsSummaryByUserId(int $userId);
} 
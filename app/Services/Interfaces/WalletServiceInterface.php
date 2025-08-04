<?php

namespace App\Services\Interfaces;

interface WalletServiceInterface
{
    /**
     * Lấy danh sách ví của người dùng hiện tại
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCurrentUserWallets();

    /**
     * Lấy thông tin chi tiết của ví
     *
     * @param string $id ID của ví
     * @return \App\Models\Wallet|null
     */
    public function getWalletById(string $id);

    /**
     * Tạo ví mới
     *
     * @param array $data Dữ liệu ví
     * @return \App\Models\Wallet|null
     */
    public function createWallet(array $data);

    /**
     * Cập nhật thông tin ví
     *
     * @param string $id ID của ví
     * @param array $data Dữ liệu cập nhật
     * @return \App\Models\Wallet|null
     */
    public function updateWallet(string $id, array $data);

    /**
     * Xóa ví
     *
     * @param string $id ID của ví
     * @return bool
     */
    public function deleteWallet(string $id);
    
    /**
     * Lấy thông tin tóm tắt của các ví cho sidebar
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWalletsSummaryForSidebar();
} 
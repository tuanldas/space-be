<?php

namespace App\Services\Interfaces;

use App\Models\Wallet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WalletServiceInterface
{
    /**
     * Lấy danh sách ví của người dùng hiện tại
     */
    public function getCurrentUserWallets(): LengthAwarePaginator;

    /**
     * Lấy thông tin chi tiết của ví
     */
    public function getWalletById(string $id): ?Wallet;

    /**
     * Tạo ví mới
     */
    public function createWallet(array $data): ?Wallet;

    /**
     * Cập nhật thông tin ví
     */
    public function updateWallet(string $id, array $data): ?Wallet;

    /**
     * Xóa ví
     */
    public function deleteWallet(string $id): bool;
    
    /**
     * Lấy thông tin tóm tắt của các ví cho sidebar
     */
    public function getWalletsSummaryForSidebar(): Collection;

    /**
     * Lấy options ví (id, name) cho user hiện tại
     */
    public function getOptions(?string $search = null, int $limit = 20): Collection;
} 
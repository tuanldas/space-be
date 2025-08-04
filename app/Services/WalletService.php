<?php

namespace App\Services;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\Interfaces\WalletServiceInterface;
use Illuminate\Support\Facades\Auth;

class WalletService implements WalletServiceInterface
{
    protected $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * Lấy danh sách ví của người dùng hiện tại
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCurrentUserWallets()
    {
        return $this->walletRepository->getWalletsByUserId(Auth::id());
    }

    /**
     * Lấy thông tin chi tiết của ví
     *
     * @param string $id ID của ví
     * @return \App\Models\Wallet|null
     */
    public function getWalletById(string $id)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return null;
            }
            
            return $wallet;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tạo ví mới
     *
     * @param array $data Dữ liệu ví
     * @return \App\Models\Wallet|null
     */
    public function createWallet(array $data)
    {
        $data['user_id'] = Auth::id();
        $data['created_by'] = Auth::id();
        $data['balance'] = $data['balance'] ?? 0;

        return $this->walletRepository->create($data);
    }

    /**
     * Cập nhật thông tin ví
     *
     * @param string $id ID của ví
     * @param array $data Dữ liệu cập nhật
     * @return \App\Models\Wallet|null
     */
    public function updateWallet(string $id, array $data)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return null;
            }
            
            if ($this->walletRepository->updateByUuid($id, $data)) {
                return $this->walletRepository->findByUuid($id);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Xóa ví
     *
     * @param string $id ID của ví
     * @return bool
     */
    public function deleteWallet(string $id)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return false;
            }
            
            return $this->walletRepository->deleteByUuid($id);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy thông tin tóm tắt của các ví cho sidebar
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWalletsSummaryForSidebar()
    {
        try {
            return $this->walletRepository->getWalletsSummaryByUserId(Auth::id());
        } catch (\Exception $e) {
            return collect();
        }
    }
} 
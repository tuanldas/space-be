<?php

namespace App\Services;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\Interfaces\WalletServiceInterface;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\TransactionType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WalletService implements WalletServiceInterface
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private WalletTransactionServiceInterface $transactionService,
        private TransactionCategoryServiceInterface $transactionCategoryService,
    ) {
    }

    /**
     * Lấy danh sách ví của người dùng hiện tại
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCurrentUserWallets(): LengthAwarePaginator
    {
        return $this->walletRepository->getWalletsByUserId(Auth::id());
    }

    /**
     * Lấy options ví (id, name) cho user hiện tại
     */
    public function getOptions(?string $search = null, int $limit = 20): Collection
    {
        try {
            return $this->walletRepository->getOptionsByUser(Auth::id(), $search, $limit);
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Lấy thông tin chi tiết của ví
     *
     * @param string $id ID của ví
     * @return \App\Models\Wallet|null
     */
    public function getWalletById(string $id): ?Wallet
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
    public function createWallet(array $data): ?Wallet
    {
        $initialBalance = (float) ($data['balance'] ?? 0);
        $data['user_id'] = Auth::id();
        $data['created_by'] = Auth::id();
        $data['balance'] = $initialBalance > 0 ? 0 : ($data['balance'] ?? 0);

        try {
            return DB::transaction(function () use ($data, $initialBalance) {
                $wallet = $this->walletRepository->create($data);

                if (!$wallet) {
                    return null;
                }

                if ($initialBalance > 0) {
                    $preferred = $this->transactionCategoryService->getFirstDefaultByType(TransactionType::INCOME->value);

                    if ($preferred) {
                        $this->transactionService->createTransaction([
                            'wallet_id' => $wallet->id,
                            'category_id' => $preferred->id,
                            'amount' => $initialBalance,
                            'transaction_date' => now()->format('Y-m-d H:i:s'),
                            'transaction_type' => TransactionType::INCOME->value,
                            'description' => __('messages.wallet_transaction.initial_balance'),
                        ]);
                    }
                }

                return $this->walletRepository->findByUuid($wallet->id);
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cập nhật thông tin ví
     *
     * @param string $id ID của ví
     * @param array $data Dữ liệu cập nhật
     * @return \App\Models\Wallet|null
     */
    public function updateWallet(string $id, array $data): ?Wallet
    {
        try {
            $wallet = $this->walletRepository->findByUuid($id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return null;
            }
            
            $allowedKeys = ['name' => true, 'currency' => true];
            $updateData = array_intersect_key($data, $allowedKeys);

            if (array_key_exists('currency', $updateData) && $updateData['currency'] !== null) {
                $updateData['currency'] = strtoupper($updateData['currency']);
                if ($updateData['currency'] !== $wallet->currency) {
                    $hasTransactions = $wallet->transactions()->exists();
                    if ($hasTransactions) {
                        unset($updateData['currency']);
                    }
                }
            }

            if (empty($updateData)) {
                return $wallet;
            }
            
            if ($this->walletRepository->updateByUuid($id, $updateData)) {
                return $this->walletRepository->findByUuid($id);
            }
            
            return $wallet;
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
    public function deleteWallet(string $id): bool
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
    public function getWalletsSummaryForSidebar(): Collection
    {
        try {
            return $this->walletRepository->getWalletsSummaryByUserId(Auth::id());
        } catch (\Exception $e) {
            return collect();
        }
    }
} 

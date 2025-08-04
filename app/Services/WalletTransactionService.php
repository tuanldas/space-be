<?php

namespace App\Services;

use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletTransactionService implements WalletTransactionServiceInterface
{
    protected $transactionRepository;
    protected $walletRepository;

    public function __construct(
        WalletTransactionRepositoryInterface $transactionRepository,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * Lấy danh sách giao dịch của ví
     *
     * @param string $walletId ID của ví
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByWalletId(string $walletId)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return new LengthAwarePaginator([], 0, 15, 1);
            }

            return $this->transactionRepository->getTransactionsByWalletId($walletId);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }

    /**
     * Lấy thông tin chi tiết của giao dịch
     *
     * @param string $id ID của giao dịch
     * @return \App\Models\WalletTransaction|null
     */
    public function getTransactionById(string $id)
    {
        try {
            $transaction = $this->transactionRepository->findByUuid($id);
            
            if (!$transaction) {
                return null;
            }
            
            $wallet = $this->walletRepository->findByUuid($transaction->wallet_id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return null;
            }
            
            return $transaction;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tạo giao dịch mới
     *
     * @param array $data Dữ liệu giao dịch
     * @return \App\Models\WalletTransaction|null
     */
    public function createTransaction(array $data)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($data['wallet_id']);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return null;
            }
            
            return DB::transaction(function () use ($data, $wallet) {
                $data['created_by'] = Auth::id();
                
                $amount = $data['amount'];
                
                if ($data['transaction_type'] === 'expense') {
                    $amount *= -1;
                }
                
                $transaction = $this->transactionRepository->create($data);
                
                if ($transaction) {
                    $this->walletRepository->updateBalance($wallet->id, $amount);
                }
                
                return $transaction;
            });
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Xóa giao dịch
     *
     * @param string $id ID của giao dịch
     * @return bool
     */
    public function deleteTransaction(string $id)
    {
        try {
            $transaction = $this->transactionRepository->findByUuid($id);
            
            if (!$transaction) {
                return false;
            }
            
            $wallet = $this->walletRepository->findByUuid($transaction->wallet_id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return false;
            }
            
            return DB::transaction(function () use ($transaction, $wallet) {
                $amount = $transaction->amount;
                
                if ($transaction->transaction_type === 'income') {
                    $amount *= -1;
                }
                
                $deleted = $this->transactionRepository->deleteByUuid($transaction->id);
                
                if ($deleted) {
                    $this->walletRepository->updateBalance($wallet->id, $amount);
                }
                
                return $deleted;
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách giao dịch theo loại
     *
     * @param string $walletId ID của ví
     * @param string $type Loại giao dịch
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByType(string $walletId, string $type)
    {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return new LengthAwarePaginator([], 0, 15, 1);
            }
            
            return $this->transactionRepository->getTransactionsByType($walletId, $type);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }

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
    ) {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return new LengthAwarePaginator([], 0, 15, 1);
            }
            
            return $this->transactionRepository->getTransactionsByDateRange($walletId, $startDate, $endDate);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }
} 
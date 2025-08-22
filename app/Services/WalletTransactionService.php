<?php

namespace App\Services;

use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\TransactionType;
use App\Support\ServiceResult;
use Symfony\Component\HttpFoundation\Response;

class WalletTransactionService implements WalletTransactionServiceInterface
{
    public function __construct(
        private WalletTransactionRepositoryInterface $transactionRepository,
        private WalletRepositoryInterface $walletRepository,
    ) {
    }

    public function getTransactions(string $walletId): ServiceResult
    {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
            }

            $data = $this->transactionRepository->getTransactions($walletId);

            return ServiceResult::success($data);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    /**
     * Lấy danh sách giao dịch của người dùng hiện tại
     */
    public function getUserTransactions(): ServiceResult
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return ServiceResult::error(__('messages.unauthenticated'), Response::HTTP_UNAUTHORIZED);
            }

            $data = $this->transactionRepository->getUserTransactions($userId);
            return ServiceResult::success($data);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    public function getTransactionById(string $id): ServiceResult
    {
        try {
            $transaction = $this->transactionRepository->findByUuid($id);
            
            if (!$transaction) {
                return ServiceResult::error(__('messages.wallet_transaction.not_found'), Response::HTTP_NOT_FOUND);
            }
            
            $wallet = $this->walletRepository->findByUuid($transaction->wallet_id);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return ServiceResult::error(__('messages.wallet_transaction.not_found'), Response::HTTP_NOT_FOUND);
            }
            
            return ServiceResult::success($transaction);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    public function createTransaction(array $data): ServiceResult
    {
        try {
            $wallet = $this->walletRepository->findByUuid($data['wallet_id']);
            
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
            }
            
            $transaction = DB::transaction(function () use ($data, $wallet) {
                $data['created_by'] = Auth::id();
                
                $amount = $data['amount'];
                
                if ($data['transaction_type'] === TransactionType::EXPENSE->value) {
                    $amount *= -1;
                }
                
                $transaction = $this->transactionRepository->create($data);
                
                if ($transaction) {
                    $this->walletRepository->updateBalance($wallet->id, $amount);
                }
                
                return $transaction;
            });

            if (!$transaction) {
                return ServiceResult::error(__('messages.error'));
            }

            return ServiceResult::success($transaction, __('messages.wallet_transaction.created'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    public function updateTransaction(string $walletId, string $transactionId, array $data): ServiceResult
    {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
            }

            $transaction = $this->transactionRepository->findByUuid($transactionId);
            if (!$transaction || $transaction->wallet_id !== $wallet->id) {
                return ServiceResult::error(__('messages.wallet_transaction.not_found'), Response::HTTP_NOT_FOUND);
            }

            $allowedKeys = ['amount' => true, 'transaction_type' => true, 'category_id' => true, 'description' => true];
            $updateData = array_intersect_key($data, $allowedKeys);

            if (empty($updateData)) {
                return ServiceResult::success($transaction, __('messages.wallet_transaction.updated'));
            }

            $oldAmount = (float) $transaction->amount;
            $oldType = $transaction->transaction_type;
            $oldSigned = $oldAmount * ($oldType === TransactionType::EXPENSE->value ? -1 : 1);

            $newAmount = isset($updateData['amount']) ? (float) $updateData['amount'] : $oldAmount;
            $newType = $updateData['transaction_type'] ?? $oldType;
            $newSigned = $newAmount * ($newType === TransactionType::EXPENSE->value ? -1 : 1);
            $delta = $newSigned - $oldSigned;

            $updated = DB::transaction(function () use ($wallet, $transactionId, $updateData, $delta) {
                if ($delta !== 0.0) {
                    $this->walletRepository->updateBalance($wallet->id, $delta);
                }

                $this->transactionRepository->updateByUuid($transactionId, $updateData);

                return $this->transactionRepository->findByUuid($transactionId);
            });

            return ServiceResult::success($updated, __('messages.wallet_transaction.updated'));
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    public function deleteTransaction(string $walletId, string $transactionId): ServiceResult
    {
        try {
            $wallet = $this->walletRepository->findByUuid($walletId);
            if (!$wallet || $wallet->user_id !== Auth::id()) {
                return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
            }

            $transaction = $this->transactionRepository->findByUuid($transactionId);
            if (!$transaction || $transaction->wallet_id !== $wallet->id) {
                return ServiceResult::error(__('messages.wallet_transaction.not_found'), Response::HTTP_NOT_FOUND);
            }

            $deleted = DB::transaction(function () use ($transaction, $wallet) {
                $amount = $transaction->amount;
                if ($transaction->transaction_type === TransactionType::INCOME->value) {
                    $amount *= -1;
                }

                $deleted = $this->transactionRepository->deleteByUuid($transaction->id);
                if ($deleted) {
                    $this->walletRepository->updateBalance($wallet->id, $amount);
                }

                return $deleted;
            });

            if (!$deleted) {
                return ServiceResult::error(__('messages.error'));
            }

            $freshWallet = $this->walletRepository->findByUuid($wallet->id);

            return ServiceResult::success([
                'transaction_id' => $transaction->id,
                'wallet_balance' => $freshWallet?->balance,
            ], __('messages.wallet_transaction.deleted'));
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }
} 

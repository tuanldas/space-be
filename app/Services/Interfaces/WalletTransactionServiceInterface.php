<?php

namespace App\Services\Interfaces;

use App\Support\ServiceResult;

interface WalletTransactionServiceInterface
{
    public function getTransactionsByWalletId(string $walletId): ServiceResult;

    public function getTransactionById(string $id): ServiceResult;

    public function createTransaction(array $data): ServiceResult;

    public function updateTransaction(string $walletId, string $transactionId, array $data): ServiceResult;

    public function deleteTransaction(string $walletId, string $transactionId): ServiceResult;

    public function getTransactionsByType(string $walletId, string $type): ServiceResult;

    public function getTransactionsByDateRange(
        string $walletId,
        string $startDate,
        string $endDate
    ): ServiceResult;
} 
<?php

namespace App\Services\Interfaces;

use App\Support\ServiceResult;

interface WalletTransactionServiceInterface
{
    public function getTransactions(string $walletId): ServiceResult;

    public function getUserTransactions(): ServiceResult;

    public function getTransactionById(string $id): ServiceResult;

    public function createTransaction(array $data): ServiceResult;

    public function updateTransaction(string $walletId, string $transactionId, array $data): ServiceResult;

    public function deleteTransaction(string $walletId, string $transactionId): ServiceResult;
} 
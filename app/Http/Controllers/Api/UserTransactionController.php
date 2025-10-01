<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalletTransaction\IndexUserTransactionsRequest;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use Illuminate\Http\JsonResponse;

class UserTransactionController extends Controller
{
    public function __construct(private WalletTransactionServiceInterface $transactionService)
    {
    }

    /**
     * @deprecated Sử dụng /api/transactions thay thế
     */
    public function index(IndexUserTransactionsRequest $request): JsonResponse
    {
        $request->validated();
        $result = $this->transactionService->getUserTransactions();

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        return response()->json([
            'success' => true,
            'data' => $result->getData(),
        ], $result->getStatus());
    }

    /**
     * Lấy chi tiết giao dịch của user theo ID
     * @deprecated Sử dụng /api/transactions/{id} thay thế
     */
    public function show(string $transactionId): JsonResponse
    {
        $result = $this->transactionService->getTransactionById($transactionId);

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        return response()->json([
            'success' => true,
            'data' => $result->getData(),
        ], $result->getStatus());
    }
} 
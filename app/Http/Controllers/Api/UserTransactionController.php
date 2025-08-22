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
} 
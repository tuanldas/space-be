<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalletTransaction\CreateTransactionRequest;
use App\Http\Requests\Api\WalletTransaction\UpdateTransactionRequest;
use App\Http\Requests\Api\WalletTransaction\IndexTransactionsRequest;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use Illuminate\Http\JsonResponse;

class WalletTransactionController extends Controller
{
    protected $transactionService;

    public function __construct(WalletTransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(IndexTransactionsRequest $request, string $wallet): JsonResponse
    {
        $request->validated();
        $result = $this->transactionService->getTransactions($wallet);

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

    public function store(CreateTransactionRequest $request, string $wallet): JsonResponse
    {
        $payload = array_merge($request->validated(), ['wallet_id' => $wallet]);
        $result = $this->transactionService->createTransaction($payload);

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        return response()->json([
            'success' => true,
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->getStatus());
    }

    public function show(string $wallet, string $transaction): JsonResponse
    {
        $result = $this->transactionService->getTransactionById($transaction);

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        $data = $result->getData();
        if ($data && isset($data->wallet_id) && $data->wallet_id !== $wallet) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet_transaction.not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], $result->getStatus());
    }

    public function update(UpdateTransactionRequest $request, string $wallet, string $transaction): JsonResponse
    {
        $result = $this->transactionService->updateTransaction($wallet, $transaction, $request->validated());

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        return response()->json([
            'success' => true,
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->getStatus());
    }

    public function destroy(string $wallet, string $transaction): JsonResponse
    {
        $result = $this->transactionService->deleteTransaction($wallet, $transaction);

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], $result->getStatus());
        }

        return response()->json([
            'success' => true,
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->getStatus());
    }
}

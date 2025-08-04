<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalletTransaction\CreateTransactionRequest;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    protected $transactionService;

    public function __construct(WalletTransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Lấy danh sách giao dịch của ví
     *
     * @param string $walletId
     * @return JsonResponse
     */
    public function index(string $walletId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByWalletId($walletId);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Tạo giao dịch mới
     *
     * @param CreateTransactionRequest $request
     * @return JsonResponse
     */
    public function store(CreateTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->createTransaction($request->validated());

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet_transaction.wallet_not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet_transaction.created'),
            'data' => $transaction
        ], 201);
    }

    /**
     * Lấy chi tiết giao dịch
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionById($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet_transaction.not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Xóa giao dịch
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->transactionService->deleteTransaction($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet_transaction.not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet_transaction.deleted')
        ]);
    }

    /**
     * Lấy danh sách giao dịch theo loại
     *
     * @param string $walletId
     * @param string $type
     * @return JsonResponse
     */
    public function getByType(string $walletId, string $type): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByType($walletId, $type);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Lấy danh sách giao dịch trong khoảng thời gian
     *
     * @param Request $request
     * @param string $walletId
     * @return JsonResponse
     */
    public function getByDateRange(Request $request, string $walletId): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $transactions = $this->transactionService->getTransactionsByDateRange(
            $walletId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}

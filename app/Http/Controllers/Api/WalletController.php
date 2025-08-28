<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\CreateWalletRequest;
use App\Http\Requests\Api\Wallet\UpdateWalletRequest;
use App\Http\Requests\Api\Wallet\GetWalletOptionsRequest;
use App\Services\Interfaces\WalletServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletServiceInterface $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Lấy danh sách ví của người dùng hiện tại
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $wallets = $this->walletService->getCurrentUserWallets();

        return response()->json([
            'success' => true,
            'data' => $wallets
        ]);
    }

    /**
     * API options ví (id, name) dùng cho typeahead/filter
     */
    public function options(GetWalletOptionsRequest $request): JsonResponse
    {
        $data = $this->walletService->getOptions(
            $request->query('search'),
            (int) $request->query('limit', 20)
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Tạo ví mới
     *
     * @param CreateWalletRequest $request
     * @return JsonResponse
     */
    public function store(CreateWalletRequest $request): JsonResponse
    {
        $wallet = $this->walletService->createWallet($request->validated());

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error')
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet.created'),
            'data' => $wallet
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết của ví
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $wallet = $this->walletService->getWalletById($id);

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet.not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $wallet
        ]);
    }

    /**
     * Cập nhật thông tin ví
     *
     * @param UpdateWalletRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateWalletRequest $request, string $id): JsonResponse
    {
        $wallet = $this->walletService->updateWallet($id, $request->validated());

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet.not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet.updated'),
            'data' => $wallet
        ]);
    }

    /**
     * Xóa ví
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->walletService->deleteWallet($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => __('messages.wallet.not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.wallet.deleted')
        ]);
    }
    
    /**
     * Lấy thông tin tóm tắt của các ví cho sidebar
     *
     * @return JsonResponse
     */
    public function getSummaryForSidebar(): JsonResponse
    {
        $wallets = $this->walletService->getWalletsSummaryForSidebar();
        
        return response()->json([
            'success' => true,
            'data' => $wallets
        ]);
    }
}

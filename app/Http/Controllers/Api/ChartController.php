<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chart\GetMonthlyExpensesRequest;
use App\Http\Requests\Api\Chart\GetTopCategoriesRequest;
use App\Services\Interfaces\ChartServiceInterface;
use Illuminate\Http\JsonResponse;

class ChartController extends Controller
{
    public function __construct(private ChartServiceInterface $chartService)
    {
    }

    /**
     * Lấy chi tiêu theo tuần trong tháng
     */
    public function monthlyExpenses(GetMonthlyExpensesRequest $request): JsonResponse
    {
        $month = $request->query('month');
        $walletId = $request->query('wallet_id');

        $result = $this->chartService->getMonthlyExpenses($month, $walletId);

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
     * Lấy top categories chi tiêu nhiều nhất
     */
    public function topCategories(GetTopCategoriesRequest $request): JsonResponse
    {
        $month = $request->query('month');
        $walletId = $request->query('wallet_id');
        $limit = (int) $request->query('limit', 5);

        $result = $this->chartService->getTopCategories($month, $walletId, $limit);

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


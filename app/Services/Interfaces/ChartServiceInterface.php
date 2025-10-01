<?php

namespace App\Services\Interfaces;

use App\Support\ServiceResult;

interface ChartServiceInterface
{
    /**
     * Lấy chi tiêu theo tuần trong tháng
     */
    public function getMonthlyExpenses(?string $month = null, ?string $walletId = null): ServiceResult;

    /**
     * Lấy top categories (cả income và expense)
     */
    public function getTopCategories(?string $month = null, ?string $walletId = null, int $limit = 5): ServiceResult;

    /**
     * Lấy net income/expense trong tháng (tổng thu - tổng chi)
     */
    public function getNetInMonth(?string $month = null, ?string $walletId = null): ServiceResult;
}


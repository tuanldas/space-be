<?php

namespace App\Services\Interfaces;

use App\Support\ServiceResult;

interface ChartServiceInterface
{
    /**
     * Lấy chi tiêu theo tuần trong tháng
     */
    public function getMonthlyExpenses(?string $month = null, ?string $walletId = null): ServiceResult;
}


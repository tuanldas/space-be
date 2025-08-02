<?php

namespace App\Repositories;

use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;

class WalletTransactionRepository extends BaseRepository implements WalletTransactionRepositoryInterface
{
    /**
     * Lấy model để thực hiện các thao tác
     *
     * @return string
     */
    public function getModel(): string
    {
        return WalletTransaction::class;
    }

    /**
     * Lấy danh sách giao dịch của ví
     *
     * @param string $walletId ID của ví
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByWalletId(string $walletId, array $columns = ['*'])
    {
        return $this->model
            ->where('wallet_id', $walletId)
            ->orderBy('transaction_date', 'desc')
            ->paginate(config('app.pagination_limit', 15), $columns);
    }

    /**
     * Lấy danh sách giao dịch theo loại
     *
     * @param string $walletId ID của ví
     * @param string $type Loại giao dịch
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByType(string $walletId, string $type, array $columns = ['*'])
    {
        return $this->model
            ->where('wallet_id', $walletId)
            ->where('transaction_type', $type)
            ->orderBy('transaction_date', 'desc')
            ->paginate(config('app.pagination_limit', 15), $columns);
    }

    /**
     * Lấy danh sách giao dịch trong khoảng thời gian
     *
     * @param string $walletId ID của ví
     * @param string $startDate Ngày bắt đầu
     * @param string $endDate Ngày kết thúc
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactionsByDateRange(
        string $walletId,
        string $startDate,
        string $endDate,
        array $columns = ['*']
    ) {
        return $this->model
            ->where('wallet_id', $walletId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->paginate(config('app.pagination_limit', 15), $columns);
    }
} 
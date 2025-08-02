<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;

class WalletRepository extends BaseRepository implements WalletRepositoryInterface
{
    /**
     * Lấy model để thực hiện các thao tác
     *
     * @return string
     */
    public function getModel(): string
    {
        return Wallet::class;
    }

    /**
     * Lấy danh sách ví của người dùng
     *
     * @param int $userId ID của người dùng
     * @param array $columns Danh sách cột cần lấy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWalletsByUserId(int $userId, array $columns = ['*'])
    {
        return $this->model
            ->where('user_id', $userId)
            ->paginate(config('app.pagination_limit', 15), $columns);
    }

    /**
     * Cập nhật số dư ví
     *
     * @param string $id ID của ví
     * @param float $amount Số tiền cần cập nhật (dương: tăng, âm: giảm)
     * @return bool
     */
    public function updateBalance(string $id, float $amount)
    {
        return DB::transaction(function () use ($id, $amount) {
            $wallet = $this->model->lockForUpdate()->find($id);
            
            if (!$wallet) {
                return false;
            }
            
            $wallet->balance += $amount;
            return $wallet->save();
        });
    }
} 
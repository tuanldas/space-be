<?php

namespace App\Repositories;

use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

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

	public function getTransactions(string $walletId, array $columns = ['*'])
	{
		$builder = QueryBuilder::for(WalletTransaction::class)
			->where('wallet_id', $walletId)
			->allowedFilters([
				AllowedFilter::exact('type', 'transaction_type'),
				AllowedFilter::callback('date_between', function ($query, $value) {
					$start = is_array($value) ? ($value['start'] ?? null) : null;
					$end = is_array($value) ? ($value['end'] ?? null) : null;
					if ($start && $end) {
						$query->whereBetween('transaction_date', [$start, $end]);
					}
				}),
				AllowedFilter::callback('search', function ($query, $value) {
					$query->where(function ($q) use ($value) {
						$q->whereRaw('description ILIKE ?', ['%' . $value . '%'])
							->orWhereRaw('CAST(amount AS TEXT) ILIKE ?', ['%' . $value . '%']);
					});
				}),
			])
			->defaultSort('-transaction_date')
			->with([
				'category:id,name,type',
				'category.image:id,imageable_id,path,disk',
				'wallet:id,name,balance,currency',
			]);

		$limit = (int) request('per_page', config('app.pagination_limit', 15));
		$selectedColumns = [
			'id',
			'wallet_id',
			'category_id',
			'created_by',
			'amount',
			'transaction_date',
			'transaction_type',
			'description',
		];
		return $builder->paginate($limit, $selectedColumns);
	}

	/**
	 * Lấy danh sách giao dịch của người dùng (tất cả ví thuộc người dùng)
	 *
	 * @param int $userId ID người dùng
	 * @param array $columns Danh sách cột cần lấy
	 * @return \Illuminate\Pagination\LengthAwarePaginator
	 */
	public function getUserTransactions(int $userId, array $columns = ['*'])
	{
		$builder = QueryBuilder::for(WalletTransaction::class)
			->whereHas('wallet', function ($q) use ($userId) {
				$q->where('user_id', $userId);
			})
			->allowedFilters([
				AllowedFilter::exact('type', 'transaction_type'),
				AllowedFilter::callback('date_between', function ($query, $value) {
					$start = is_array($value) ? ($value['start'] ?? null) : null;
					$end = is_array($value) ? ($value['end'] ?? null) : null;
					if ($start && $end) {
						$query->whereBetween('transaction_date', [$start, $end]);
					}
				}),
				AllowedFilter::exact('wallet_id'),
				AllowedFilter::callback('search', function ($query, $value) {
					$query->where(function ($q) use ($value) {
						$q->whereRaw('description ILIKE ?', ['%' . $value . '%'])
							->orWhereRaw('CAST(amount AS TEXT) ILIKE ?', ['%' . $value . '%']);
					});
				}),
			])
			->defaultSort('-transaction_date')
			->with([
				'category:id,name,type',
				'category.image:id,imageable_id,path,disk',
				'wallet:id,name,balance,currency',
			]);

		$limit = (int) request('per_page', config('app.pagination_limit', 15));
		$selectedColumns = [
			'id',
			'wallet_id',
			'category_id',
			'created_by',
			'amount',
			'transaction_date',
			'transaction_type',
			'description',
		];
		return $builder->paginate($limit, $selectedColumns);
	}
} 
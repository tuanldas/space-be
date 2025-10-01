<?php

namespace App\Repositories;

use App\Enums\TransactionType;
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
				// alias cho date_from/date_to
				AllowedFilter::callback('date_from', function ($query, $value) {
					if ($value) {
						$query->whereDate('transaction_date', '>=', $value);
					}
				}),
				AllowedFilter::callback('date_to', function ($query, $value) {
					if ($value) {
						$query->whereDate('transaction_date', '<=', $value);
					}
				}),
				AllowedFilter::callback('search', function ($query, $value) {
					$query->where(function ($q) use ($value) {
						$q->whereRaw('description ILIKE ?', ['%' . $value . '%'])
							->orWhereRaw('CAST(amount AS TEXT) ILIKE ?', ['%' . $value . '%']);
					});
				}),
				AllowedFilter::callback('min_amount', function ($query, $value) {
					if ($value !== null && $value !== '') {
						$query->where('amount', '>=', (float) $value);
					}
				}),
				AllowedFilter::callback('max_amount', function ($query, $value) {
					if ($value !== null && $value !== '') {
						$query->where('amount', '<=', (float) $value);
					}
				}),
			])
			->allowedSorts(['transaction_date', 'amount'])
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
				// alias cho date_from/date_to
				AllowedFilter::callback('date_from', function ($query, $value) {
					if ($value) {
						$query->whereDate('transaction_date', '>=', $value);
					}
				}),
				AllowedFilter::callback('date_to', function ($query, $value) {
					if ($value) {
						$query->whereDate('transaction_date', '<=', $value);
					}
				}),
				AllowedFilter::exact('wallet_id'),
				AllowedFilter::exact('category_id'),
				AllowedFilter::callback('category_ids', function ($query, $value) {
					$ids = is_array($value) ? $value : explode(',', (string) $value);
					$ids = array_filter($ids, fn($v) => (string) $v !== '');
					if (!empty($ids)) {
						$query->whereIn('category_id', $ids);
					}
				}),
				AllowedFilter::callback('search', function ($query, $value) {
					$query->where(function ($q) use ($value) {
						$q->whereRaw('description ILIKE ?', ['%' . $value . '%'])
							->orWhereRaw('CAST(amount AS TEXT) ILIKE ?', ['%' . $value . '%']);
					});
				}),
				AllowedFilter::callback('min_amount', function ($query, $value) {
					if ($value !== null && $value !== '') {
						$query->where('amount', '>=', (float) $value);
					}
				}),
				AllowedFilter::callback('max_amount', function ($query, $value) {
					if ($value !== null && $value !== '') {
						$query->where('amount', '<=', (float) $value);
					}
				}),
			])
			->allowedSorts(['transaction_date', 'amount'])
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
	 * Lấy tổng chi tiêu theo khoảng thời gian
	 */
	public function getExpensesByDateRange(int $userId, string $startDate, string $endDate, ?string $walletId = null): array
	{
		$query = $this->model
			->whereHas('wallet', function ($q) use ($userId) {
				$q->where('user_id', $userId);
			})
			->where('transaction_type', TransactionType::EXPENSE->value)
			->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

		if ($walletId) {
			$query->where('wallet_id', $walletId);
		}

		$total = $query->sum('amount');
		$count = $query->count();

		return [
			'total' => (float) $total,
			'count' => $count,
		];
	}

	/**
	 * Lấy top categories (cả income và expense)
	 */
	public function getTopCategories(int $userId, string $startDate, string $endDate, ?string $walletId = null, int $limit = 5): array
	{
		$query = $this->model
			->select([
				'category_id',
				\DB::raw('SUM(amount) as total'),
				\DB::raw('COUNT(*) as transaction_count')
			])
			->with(['category:id,name,type,user_id,is_default'])
			->whereHas('wallet', function ($q) use ($userId) {
				$q->where('user_id', $userId);
			})
			->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
			->groupBy('category_id')
			->orderByDesc('total')
			->limit($limit);

		if ($walletId) {
			$query->where('wallet_id', $walletId);
		}

		$results = $query->get();

		return $results->map(function ($item) {
			return [
				'category_id' => $item->category_id,
				'category_name' => $item->category->name ?? null,
				'category_type' => $item->category->type ?? null,
				'category_image' => $item->category->image ?? null,
				'total' => (float) $item->total,
				'transaction_count' => $item->transaction_count,
			];
		})->toArray();
	}
} 
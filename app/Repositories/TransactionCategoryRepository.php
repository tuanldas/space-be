<?php

namespace App\Repositories;

use App\Models\TransactionCategory;
use App\Repositories\Interfaces\TransactionCategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<TransactionCategory>
 * @implements TransactionCategoryRepositoryInterface
 */
class TransactionCategoryRepository extends BaseRepository implements TransactionCategoryRepositoryInterface
{
    public function getModel()
    {
        return TransactionCategory::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function getAllByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->ofType($type)->paginate($perPage);
    }

    public function getAllDefaultCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->default()->paginate($perPage);
    }

    public function getAllByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)->paginate($perPage);
    }

    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('type', $type)
            ->paginate($perPage);
    }

    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->onlyTrashed()->paginate($perPage);
    }

    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->onlyTrashed()
            ->where('user_id', $userId)
            ->paginate($perPage);
    }

    public function restore(string $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(string $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }
} 
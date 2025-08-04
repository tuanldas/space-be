<?php

namespace App\Repositories;

use App\Models\Image;
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

    public function findTrashedByUuid(
        string $id,
        array  $columns = ['*'],
        array  $relations = [],
        array  $appends = []
    ): ?TransactionCategory
    {
        $model = $this->model->withTrashed()
            ->where('id', $id)
            ->select($columns)
            ->with($relations)
            ->first();

        if ($model && !empty($appends)) {
            $model->append($appends);
        }

        return $model;
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
        return $this->model->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('is_default', true);
        })->paginate($perPage);
    }

    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('type', $type)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('is_default', true);
            })
            ->paginate($perPage);
    }

    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->onlyTrashed()->paginate($perPage);
    }

    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->onlyTrashed()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('is_default', true);
            })
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

    public function attachImage(TransactionCategory $category, array $imageData): Image
    {
        return $category->image()->create($imageData);
    }

    public function updateImage(TransactionCategory $category, array $imageData): ?Image
    {
        $image = $category->image;

        if (!$image) {
            return $this->attachImage($category, $imageData);
        }

        $image->update($imageData);
        return $image->fresh();
    }

    public function removeImage(TransactionCategory $category): bool
    {
        $image = $category->image;

        if (!$image) {
            return false;
        }

        return $image->delete();
    }
} 

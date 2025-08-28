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
        return $this->model
            ->ofType($type)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getAllDefaultCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->default()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getAllByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('is_default', true);
        })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('is_default', true);
            })
            ->ofType($type)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->onlyTrashed()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function restore(string $id): bool
    {
        $model = $this->findTrashedByUuid($id);

        if ($model) {
            $model->restore();
            return true;
        }

        return false;
    }

    public function forceDelete(string $id): bool
    {
        $model = $this->findTrashedByUuid($id);

        if ($model) {
            $model->forceDelete();
            return true;
        }

        return false;
    }

    public function attachImage(TransactionCategory $category, array $imageData): Image
    {
        return $category->image()->create($imageData);
    }

    public function updateImage(TransactionCategory $category, array $imageData): ?Image
    {
        if ($category->image) {
            $category->image->update($imageData);
            return $category->image;
        }

        return null;
    }

    public function removeImage(TransactionCategory $category): bool
    {
        if ($category->image) {
            return $category->image->delete();
        }

            return false;
        }

    /**
     * Lấy danh mục mặc định đầu tiên theo type (không phân trang)
     */
    public function getFirstDefaultByType(string $type): ?TransactionCategory
    {
        return $this->model
            ->default()
            ->ofType($type)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Lấy options danh mục (id, name) theo user, có search, type và limit
     */
    public function getOptions(int $userId, ?string $search = null, ?string $type = null, int $limit = 20)
    {
        $query = $this->model
            ->select(['id', 'name'])
            ->when($type, fn($q) => $q->ofType($type))
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('is_default', true);
            })
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        return $query->limit($limit)->get();
    }
} 

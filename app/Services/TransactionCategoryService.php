<?php

namespace App\Services;

use App\Adapters\Interfaces\FileAdapterInterface;
use App\Enums\ImageCategoryType;
use App\Models\Image;
use App\Models\TransactionCategory;
use App\Repositories\Interfaces\TransactionCategoryRepositoryInterface;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionCategoryService implements TransactionCategoryServiceInterface
{
    public function __construct(
        private TransactionCategoryRepositoryInterface $repository,
        private FileAdapterInterface $fileAdapter
    ) {
    }

    public function getById(string $id): TransactionCategory
    {
        return $this->repository->findByUuid($id);
    }
    
    public function findTrashedByUuid(
        string $id,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?TransactionCategory {
        return $this->repository->findTrashedByUuid($id, $columns, $relations, $appends);
    }

    public function create(array $data): TransactionCategory
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->repository->updateByUuid($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->deleteByUuid($id);
    }

    public function getAllByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllByType($type, $perPage);
    }

    public function getAllDefaultCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllDefaultCategories($perPage);
    }

    public function getAllByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllByUser($userId, $perPage);
    }

    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllByUserAndType($userId, $type, $perPage);
    }

    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getTrashedByUser($userId, $perPage);
    }

    public function restore(string $id): bool
    {
        return $this->repository->restore($id);
    }

    public function forceDelete(string $id): bool
    {
        return $this->repository->forceDelete($id);
    }
    
    public function attachImage(string $categoryId, UploadedFile $imageFile, int $userId): Image
    {
        $category = $this->repository->findByUuid($categoryId);
        
        $imageData = [
            'user_id' => $userId,
            'disk' => '',
            'path' => '',
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId,
            'type' => ImageCategoryType::CATEGORY_IMAGE,
        ];

        if ($category->image) {
            $this->fileAdapter->delete($category->image->path, $category->image->disk);
        }

        $fileInfo = $this->fileAdapter->store($imageFile, 'transaction-categories');
        $imageData['disk'] = $fileInfo['disk'] ?? 'public';
        $imageData['path'] = $fileInfo['path'] ?? '';
        
        return $this->repository->attachImage($category, $imageData);
    }
    
    public function updateImage(string $categoryId, UploadedFile $imageFile, int $userId): ?Image
    {
        $category = $this->repository->findByUuid($categoryId);
        
        if ($category->image) {
            $this->fileAdapter->delete($category->image->path, $category->image->disk);
        }
        
        $fileInfo = $this->fileAdapter->store($imageFile, 'transaction-categories');
        
        $imageData = [
            'user_id' => $userId,
            'disk' => $fileInfo['disk'] ?? 'public',
            'path' => $fileInfo['path'] ?? '',
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $categoryId,
            'type' => ImageCategoryType::CATEGORY_IMAGE,
        ];
        
        if ($category->image) {
        return $this->repository->updateImage($category, $imageData);
        }

        return $this->repository->attachImage($category, $imageData);
    }
    
    public function removeImage(string $categoryId): bool
    {
        $category = $this->repository->findTrashedByUuid($categoryId) ?? $this->repository->findByUuid($categoryId);
            
        if ($category && $category->image) {
                $this->fileAdapter->delete($category->image->path, $category->image->disk);
            }
            
        return $category ? $this->repository->removeImage($category) : false;
        }

    public function getFirstDefaultByType(string $type): ?TransactionCategory
    {
        return $this->repository->getFirstDefaultByType($type);
    }
} 

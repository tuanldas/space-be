<?php

namespace App\Services;

use App\Models\TransactionCategory;
use App\Repositories\Interfaces\TransactionCategoryRepositoryInterface;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionCategoryService implements TransactionCategoryServiceInterface
{
    public function __construct(
        private TransactionCategoryRepositoryInterface $repository
    ) {
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getById(string $id): TransactionCategory
    {
        return $this->repository->findByUuid($id);
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

    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getTrashed($perPage);
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
} 
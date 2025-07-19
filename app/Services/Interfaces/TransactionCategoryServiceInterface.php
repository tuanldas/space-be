<?php

namespace App\Services\Interfaces;

use App\Models\TransactionCategory;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionCategoryServiceInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function getById(string $id): TransactionCategory;

    public function create(array $data): TransactionCategory;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;

    public function getAllByType(string $type, int $perPage = 15): LengthAwarePaginator;

    public function getAllDefaultCategories(int $perPage = 15): LengthAwarePaginator;

    public function getAllByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator;

    public function getTrashed(int $perPage = 15): LengthAwarePaginator;

    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function restore(string $id): bool;

    public function forceDelete(string $id): bool;
} 
<?php

namespace App\Repositories\Interfaces;

use App\Models\TransactionCategory;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends EloquentRepositoryInterface<TransactionCategory>
 */
interface TransactionCategoryRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * @param string $uuid
     * @param array $payload
     * @return bool
     */
    public function updateByUuid(string $uuid, array $payload): bool;

    /**
     * @param string $uuid
     * @return bool
     */
    public function deleteByUuid(string $uuid): bool;

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    /**
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllByType(string $type, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllDefaultCategories(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $userId
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllByUserAndType(int $userId, string $type, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTrashedByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool;

    /**
     * @param string $id
     * @return bool
     */
    public function forceDelete(string $id): bool;
} 
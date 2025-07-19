<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template T of Model
 */
interface EloquentRepositoryInterface
{
    /**
     * Get all models.
     * 
     * @param array $columns
     * @param array $relations
     * @return Collection<T>
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find model by id.
     * 
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return T|null
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    /**
     * Find model by uuid.
     * 
     * @param string $uuid
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return T|null
     */
    public function findByUuid(
        string $uuid,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    /**
     * Create a model.
     * 
     * @param array $payload
     * @return T|null
     */
    public function create(array $payload): ?Model;

    /**
     * Update existing model.
     * 
     * @param int $modelId
     * @param array $payload
     * @return bool
     */
    public function update(int $modelId, array $payload): bool;

    /**
     * Update existing model by UUID.
     * 
     * @param string $uuid
     * @param array $payload
     * @return bool
     */
    public function updateByUuid(string $uuid, array $payload): bool;

    /**
     * Delete model by id.
     * 
     * @param int $modelId
     * @return bool
     */
    public function deleteById(int $modelId): bool;

    /**
     * Delete model by uuid.
     * 
     * @param string $uuid
     * @return bool
     */
    public function deleteByUuid(string $uuid): bool;
} 
<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Get model
     * 
     * @return string
     */
    public function getModel()
    {
        return User::class;
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function getUsersWithRoles(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when(isset($filters['search']), function ($query) use ($filters) {
                return $query->where(function ($query) use ($filters) {
                    $searchTerm = '%' . $filters['search'] . '%';
                    $query->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                });
            })
            ->when(isset($filters['role']), function ($query) use ($filters) {
                return $query->whereIs($filters['role']);
            })
            ->with(['roles' => function($query) {
                $query->select(['id', 'name', 'title']);
            }])
            ->paginate($perPage);
    }
} 
<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Silber\Bouncer\Database\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * Get model
     * 
     * @return string
     */
    public function getModel()
    {
        return Role::class;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return $this->model->with('abilities')->get();
    }

    /**
     * @inheritDoc
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = ['abilities'],
        array $appends = []
    ): ?Model {
        return parent::findById($modelId, $columns, $relations, $appends);
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?Role
    {
        return $this->model->with('abilities')->where('name', $name)->first();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Role
    {
        return $this->model->create([
            'name' => $data['name'],
            'title' => $data['title'] ?? $data['name'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $role = $this->findById($id);
        if (!$role) {
            return false;
        }

        return $role->delete();
    }

    /**
     * @inheritDoc
     */
    public function getUsersByRole(string $roleName, int $perPage = 15): LengthAwarePaginator
    {
        return User::whereIs($roleName)->paginate($perPage);
    }
} 
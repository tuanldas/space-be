<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AbilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\Database\Ability;
use Bouncer;

class AbilityRepository extends BaseRepository implements AbilityRepositoryInterface
{
    /**
     * Get model
     * 
     * @return string
     */
    public function getModel()
    {
        return Ability::class;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return $this->model->get();
    }

    /**
     * @inheritDoc
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model {
        return parent::findById($modelId, $columns, $relations, $appends);
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name): ?Ability
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Ability
    {
        return $this->model->create([
            'name' => $data['name'],
            'title' => $data['title'] ?? $data['name'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'only_owned' => $data['only_owned'] ?? false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $ability = $this->findById($id);
        if (!$ability) {
            return false;
        }

        return $ability->delete();
    }

    /**
     * @inheritDoc
     */
    public function getAbilitiesForRole(string $roleName): Collection
    {
        return Bouncer::abilities()->forRole($roleName)->get();
    }
} 
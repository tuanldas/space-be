<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\AbilityRepositoryInterface;
use App\Services\Interfaces\AbilityServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Silber\Bouncer\Database\Ability;
use Bouncer;

class AbilityService implements AbilityServiceInterface
{
    /**
     * AbilityService constructor.
     */
    public function __construct(
        private AbilityRepositoryInterface $abilityRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllAbilities(): Collection
    {
        return $this->abilityRepository->getAll();
    }

    /**
     * @inheritDoc
     */
    public function getAbilityById(int $id): ?Ability
    {
        $ability = $this->abilityRepository->findById($id);
        return $ability instanceof Ability ? $ability : null;
    }

    /**
     * @inheritDoc
     */
    public function getAbilityByName(string $name): ?Ability
    {
        return $this->abilityRepository->findByName($name);
    }

    /**
     * @inheritDoc
     */
    public function createAbility(array $data): Ability
    {
        return $this->abilityRepository->create([
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
    public function updateAbility(int $id, array $data): Ability
    {
        $ability = $this->getAbilityById($id);
        if (!$ability) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Ability with ID {$id} not found.");
        }

        $payload = [];
        if (isset($data['name'])) {
            $payload['name'] = $data['name'];
        }
        if (isset($data['title'])) {
            $payload['title'] = $data['title'];
        }
        if (isset($data['entity_type'])) {
            $payload['entity_type'] = $data['entity_type'];
        }
        if (isset($data['entity_id'])) {
            $payload['entity_id'] = $data['entity_id'];
        }
        if (isset($data['only_owned'])) {
            $payload['only_owned'] = $data['only_owned'];
        }
        
        $this->abilityRepository->update($id, $payload);
        return $this->getAbilityById($id);
    }

    /**
     * @inheritDoc
     */
    public function deleteAbility(int $id): bool
    {
        $ability = $this->getAbilityById($id);
        if (!$ability) {
            return false;
        }

        $rolesWithAbility = Bouncer::roles()->get()->filter(function ($role) use ($ability) {
            return Bouncer::allows($role->name, $ability->name);
        });

        if ($rolesWithAbility->count() > 0) {
            throw new \Exception('Không thể xóa quyền này vì nó đang được sử dụng bởi một số vai trò.');
        }

        return $this->abilityRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function getAbilitiesForRole(string $roleName): Collection
    {
        return $this->abilityRepository->getAbilitiesForRole($roleName);
    }

    /**
     * @inheritDoc
     */
    public function userCan(int $userId, string $ability, $model = null): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->can($ability, $model);
    }
} 
<?php

namespace App\Services;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Services\Interfaces\RoleServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Silber\Bouncer\Database\Role;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RoleService implements RoleServiceInterface
{
    /**
     * RoleService constructor.
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
    }

    /**
     * @inheritDoc
     */
    public function getRoleById(int $id): ?Role
    {
        $role = $this->roleRepository->findById($id);
        return $role instanceof Role ? $role : null;
    }

    /**
     * @inheritDoc
     */
    public function getRoleByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    /**
     * @inheritDoc
     */
    public function createRole(array $data): Role
    {
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'title' => $data['title'] ?? $data['name'],
        ]);

        if (isset($data['abilities']) && is_array($data['abilities'])) {
            $this->assignAbilitiesToRole($role->name, $data['abilities']);
        }

        return $role;
    }

    /**
     * @inheritDoc
     */
    public function updateRole(int $id, array $data): Role
    {
        $role = $this->getRoleById($id);
        if (!$role) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Role with ID {$id} not found.");
        }

        $oldName = $role->name;
        
        $payload = [];
        if (isset($data['name'])) {
            $payload['name'] = $data['name'];
        }
        if (isset($data['title'])) {
            $payload['title'] = $data['title'];
        }
        $this->roleRepository->update($id, $payload);
        $role = $this->getRoleById($id);

        if (isset($data['name']) && $oldName !== $data['name']) {
            $abilities = Bouncer::abilities()->forRole($oldName)->get();
            
            Bouncer::disallow($oldName)->everything();
            foreach ($abilities as $ability) {
                Bouncer::allow($role->name)->to($ability->name);
            }
        }

        if (isset($data['abilities']) && is_array($data['abilities'])) {
            Bouncer::disallow($role->name)->everything();
            $this->assignAbilitiesToRole($role->name, $data['abilities']);
        }

        return $role;
    }

    /**
     * @inheritDoc
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->getRoleById($id);
        if (!$role) {
            return false;
        }

        $usersWithThisRoleOnly = \App\Models\User::whereIs($role->name)
            ->whereDoesntHave('roles', function ($query) use ($role) {
                $query->where('roles.id', '!=', $role->id);
            })->count();
        
        if ($usersWithThisRoleOnly > 0) {
            throw new \Exception('Không thể xóa vai trò này vì nó là vai trò duy nhất của một số người dùng.');
        }

        Bouncer::disallow($role->name)->everything();
        
        return $this->roleRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function assignAbilitiesToRole(string $roleName, $abilities): void
    {
        if (is_array($abilities)) {
            foreach ($abilities as $ability) {
                Bouncer::allow($roleName)->to($ability);
            }
        } else {
            Bouncer::allow($roleName)->to($abilities);
        }
    }

    /**
     * @inheritDoc
     */
    public function removeAbilitiesFromRole(string $roleName, $abilities): void
    {
        if (is_array($abilities)) {
            foreach ($abilities as $ability) {
                Bouncer::disallow($roleName)->to($ability);
            }
        } else {
            Bouncer::disallow($roleName)->to($abilities);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignRoleToUser(int $userId, string $roleName): void
    {
        Bouncer::assign($roleName)->to($userId);
    }

    /**
     * @inheritDoc
     */
    public function removeRoleFromUser(int $userId, string $roleName): void
    {
        Bouncer::retract($roleName)->from($userId);
    }

    /**
     * @inheritDoc
     */
    public function getUsersByRole(string $roleName, int $perPage = 15): LengthAwarePaginator
    {
        return $this->roleRepository->getUsersByRole($roleName, $perPage);
    }
} 
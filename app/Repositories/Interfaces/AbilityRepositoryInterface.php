<?php

namespace App\Repositories\Interfaces;

use Silber\Bouncer\Database\Ability;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AbilityRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Lấy tất cả abilities
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Lấy ability theo tên
     *
     * @param string $name
     * @return Ability|null
     */
    public function findByName(string $name): ?Ability;

    /**
     * Tạo ability mới
     *
     * @param array $data
     * @return Ability
     */
    public function create(array $data): Ability;

    /**
     * Xóa ability
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy tất cả abilities của một vai trò
     *
     * @param string $roleName
     * @return Collection
     */
    public function getAbilitiesForRole(string $roleName): Collection;
} 
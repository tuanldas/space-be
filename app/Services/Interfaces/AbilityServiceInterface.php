<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Silber\Bouncer\Database\Ability;

interface AbilityServiceInterface
{
    /**
     * Lấy tất cả abilities
     *
     * @return Collection
     */
    public function getAllAbilities(): Collection;

    /**
     * Lấy ability theo ID
     *
     * @param int $id
     * @return Ability|null
     */
    public function getAbilityById(int $id): ?Ability;

    /**
     * Lấy ability theo tên
     *
     * @param string $name
     * @return Ability|null
     */
    public function getAbilityByName(string $name): ?Ability;

    /**
     * Tạo ability mới
     *
     * @param array $data
     * @return Ability
     */
    public function createAbility(array $data): Ability;

    /**
     * Cập nhật ability
     *
     * @param int $id
     * @param array $data
     * @return Ability
     */
    public function updateAbility(int $id, array $data): Ability;

    /**
     * Xóa ability
     *
     * @param int $id
     * @return bool
     */
    public function deleteAbility(int $id): bool;

    /**
     * Lấy tất cả abilities của một vai trò
     *
     * @param string $roleName
     * @return Collection
     */
    public function getAbilitiesForRole(string $roleName): Collection;

    /**
     * Kiểm tra người dùng có quyền không
     *
     * @param int $userId
     * @param string $ability
     * @param mixed $model
     * @return bool
     */
    public function userCan(int $userId, string $ability, $model = null): bool;
} 
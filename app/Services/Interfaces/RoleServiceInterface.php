<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Silber\Bouncer\Database\Role;

interface RoleServiceInterface
{
    /**
     * Lấy tất cả vai trò
     *
     * @return Collection
     */
    public function getAllRoles(): Collection;

    /**
     * Lấy vai trò theo ID
     *
     * @param int $id
     * @return Role|null
     */
    public function getRoleById(int $id): ?Role;

    /**
     * Lấy vai trò theo tên
     *
     * @param string $name
     * @return Role|null
     */
    public function getRoleByName(string $name): ?Role;

    /**
     * Tạo vai trò mới
     *
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role;

    /**
     * Cập nhật vai trò
     *
     * @param int $id
     * @param array $data
     * @return Role
     */
    public function updateRole(int $id, array $data): Role;

    /**
     * Xóa vai trò
     *
     * @param int $id
     * @return bool
     */
    public function deleteRole(int $id): bool;

    /**
     * Gán quyền cho vai trò
     *
     * @param string $roleName
     * @param array|string $abilities
     * @return void
     */
    public function assignAbilitiesToRole(string $roleName, $abilities): void;

    /**
     * Thu hồi quyền từ vai trò
     *
     * @param string $roleName
     * @param array|string $abilities
     * @return void
     */
    public function removeAbilitiesFromRole(string $roleName, $abilities): void;

    /**
     * Gán vai trò cho người dùng
     *
     * @param int $userId
     * @param string $roleName
     * @return void
     */
    public function assignRoleToUser(int $userId, string $roleName): void;

    /**
     * Thu hồi vai trò từ người dùng
     *
     * @param int $userId
     * @param string $roleName
     * @return void
     */
    public function removeRoleFromUser(int $userId, string $roleName): void;

    /**
     * Lấy danh sách người dùng theo vai trò
     *
     * @param string $roleName
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByRole(string $roleName, int $perPage = 15): LengthAwarePaginator;
} 
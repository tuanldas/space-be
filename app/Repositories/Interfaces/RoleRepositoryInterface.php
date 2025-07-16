<?php

namespace App\Repositories\Interfaces;

use Silber\Bouncer\Database\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Lấy tất cả vai trò
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll();

    /**
     * Lấy vai trò theo tên
     *
     * @param string $name
     * @return Role|null
     */
    public function findByName(string $name): ?Role;

    /**
     * Tạo vai trò mới
     *
     * @param array $data
     * @return Role
     */
    public function create(array $data): Role;

    /**
     * Xóa vai trò
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy danh sách người dùng theo vai trò
     *
     * @param string $roleName
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersByRole(string $roleName, int $perPage = 15): LengthAwarePaginator;
} 
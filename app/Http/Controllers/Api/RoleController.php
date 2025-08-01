<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\User\UserRoleRequest;
use App\Http\Requests\Api\Role\CreateRoleRequest;
use App\Http\Requests\Api\Role\UpdateRoleRequest;
use App\Services\Interfaces\RoleServiceInterface;
use App\Services\Interfaces\AbilityServiceInterface;

class RoleController extends Controller
{
    /**
     * RoleController constructor.
     */
    public function __construct(
        protected RoleServiceInterface $roleService,
        protected AbilityServiceInterface $abilityService
    ) {
    }

    /**
     * Hiển thị danh sách vai trò.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();
        
        return response()->json($roles);
    }

    /**
     * Lưu vai trò mới.
     *
     * @param CreateRoleRequest $request
     * @return JsonResponse
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = $this->roleService->createRole($validated);

        return response()->json($role, 201);
    }

    /**
     * Hiển thị thông tin vai trò cụ thể.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->getRoleById($id);
        
        if (!$role) {
            return response()->json(['message' => __('messages.role.not_found')], 404);
        }
        
        return response()->json($role);
    }

    /**
     * Cập nhật vai trò.
     *
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $role = $this->roleService->updateRole($id, $validated);
            return response()->json($role);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => __('messages.role.not_found')], 404);
        }
    }

    /**
     * Xóa vai trò.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->roleService->deleteRole($id);
            return response()->json(['message' => __('messages.role.deleted')]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
    
    /**
     * Lấy danh sách tất cả các quyền trong hệ thống.
     *
     * @return JsonResponse
     */
    public function abilities(): JsonResponse
    {
        $abilities = $this->abilityService->getAllAbilities();
        
        return response()->json($abilities);
    }
    
    /**
     * Gán vai trò cho người dùng.
     *
     * @param UserRoleRequest $request
     * @return JsonResponse
     */
    public function assignRoleToUser(UserRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $this->roleService->assignRoleToUser($validated['user_id'], $validated['role']);
        
        return response()->json([
            'message' => __('messages.role.assigned')
        ]);
    }
    
    /**
     * Thu hồi vai trò từ người dùng.
     *
     * @param UserRoleRequest $request
     * @return JsonResponse
     */
    public function removeRoleFromUser(UserRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $this->roleService->removeRoleFromUser($validated['user_id'], $validated['role']);
        
        return response()->json([
            'message' => __('messages.role.removed')
        ]);
    }
    
    /**
     * Lấy danh sách người dùng có vai trò cụ thể.
     *
     * @param Request $request
     * @param string $roleName
     * @return JsonResponse
     */
    public function getUsersByRole(Request $request, string $roleName): JsonResponse
    {
        $role = $this->roleService->getRoleByName($roleName);
        
        if (!$role) {
            return response()->json([
                'message' => __('messages.role.not_found')
            ], 404);
        }
        
        $perPage = $request->query('per_page', 15);
        $users = $this->roleService->getUsersByRole($roleName, $perPage);
        
        return response()->json($users);
    }
}

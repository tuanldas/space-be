<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\CreateUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Bouncer;

class UserController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): JsonResponse
    {
        if (!Bouncer::can('view-users')) {
            throw new AuthorizationException('Bạn không có quyền xem danh sách người dùng.');
        }

        $perPage = $request->query('per_page', 15);
        $filters = $request->only(['search']);
        
        $users = $this->userService->getAllUsers($perPage, $filters);
        
        return response()->json($users);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        if (!Bouncer::can('view-users')) {
            throw new AuthorizationException('Bạn không có quyền xem thông tin người dùng.');
        }

        try {
            $user = $this->userService->getUserById($id);
            
            return response()->json($user);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        if (!Bouncer::can('create-users')) {
            throw new AuthorizationException('Bạn không có quyền tạo người dùng mới.');
        }
        
        $userData = $request->validated();
        
        $user = $this->userService->createUser($userData);
        
        return response()->json($user, 201);
    }

    /**
     * Update the specified user in storage.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        if (!Bouncer::can('update-users')) {
            throw new AuthorizationException('Bạn không có quyền cập nhật thông tin người dùng.');
        }
        
        try {
            $userData = $request->validated();
            
            $user = $this->userService->updateUser($id, $userData);
            
            return response()->json($user);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        if (!Bouncer::can('delete-users')) {
            throw new AuthorizationException('Bạn không có quyền xóa người dùng.');
        }
        
        try {
            $deleted = $this->userService->deleteUser($id);
            
            return response()->json(['message' => 'User deleted successfully']);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
} 
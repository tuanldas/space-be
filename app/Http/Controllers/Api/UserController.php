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

class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct(
        protected UserServiceInterface $userService
    ) {
    }

    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $filters = $request->only(['search', 'role']);
        
        $users = $this->userService->getAllUsers($perPage, $filters);
        
        return response()->json($users);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            
            return response()->json($user);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => __('messages.user.not_found')], 404);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
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
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $userData = $request->validated();
            
            $user = $this->userService->updateUser($id, $userData);
            
            return response()->json($user);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => __('messages.user.not_found')], 404);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            
            return response()->json(null, 204);
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json(['message' => __('messages.user.not_found')], 404);
        }
    }
} 
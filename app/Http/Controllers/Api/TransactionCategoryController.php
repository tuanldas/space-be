<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionCategory\CreateTransactionCategoryRequest;
use App\Http\Requests\Api\TransactionCategory\UpdateTransactionCategoryRequest;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TransactionCategoryController extends Controller
{
    public function __construct(
        private TransactionCategoryServiceInterface $transactionCategoryService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $userId = Auth::id();
        $type = $request->query('type');

        if ($type) {
            $categories = $this->transactionCategoryService->getAllByUserAndType($userId, $type, $perPage);
        } else {
            $categories = $this->transactionCategoryService->getAllByUser($userId, $perPage);
        }

        return response()->json($categories);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->transactionCategoryService->getById($id);
        return response()->json($category);
    }

    public function store(CreateTransactionCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        $category = $this->transactionCategoryService->create($data);

        return response()->json($category, Response::HTTP_CREATED);
    }

    public function update(UpdateTransactionCategoryRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $this->transactionCategoryService->update($id, $data);

        $category = $this->transactionCategoryService->getById($id);
        return response()->json($category);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->transactionCategoryService->delete($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $userId = Auth::id();

        $trashedCategories = $this->transactionCategoryService->getTrashedByUser($userId, $perPage);

        return response()->json($trashedCategories);
    }

    public function restore(string $id): JsonResponse
    {
        $this->transactionCategoryService->restore($id);
        $category = $this->transactionCategoryService->getById($id);

        return response()->json($category);
    }

    public function forceDelete(string $id): JsonResponse
    {
        $this->transactionCategoryService->forceDelete($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
 
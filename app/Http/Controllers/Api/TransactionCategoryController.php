<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionCategory\CreateTransactionCategoryRequest;
use App\Http\Requests\Api\TransactionCategory\UpdateTransactionCategoryRequest;
use App\Models\TransactionCategory;
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
        
        $image = $request->file('image');
        unset($data['image']);
        
        $category = $this->transactionCategoryService->create($data);
        
        $this->transactionCategoryService->attachImage(
            $category->id,
            $image,
            Auth::id()
        );
        
        $category = $this->transactionCategoryService->getById($category->id);

        return response()->json($category, Response::HTTP_CREATED);
    }

    public function update(UpdateTransactionCategoryRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        
        $image = $request->file('image');
        if (isset($data['image'])) {
            unset($data['image']);
        }
        
        $this->transactionCategoryService->update($id, $data);
        
        if ($image) {
            $this->transactionCategoryService->updateImage(
                $id,
                $image,
                Auth::id()
            );
        }

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

    public function restore(string $transaction_category): JsonResponse
    {
        $this->transactionCategoryService->restore($transaction_category);
        $category = $this->transactionCategoryService->getById($transaction_category);

        return response()->json($category);
    }

    public function forceDelete(string $transaction_category): JsonResponse
    {
        // Kiểm tra xem danh mục có tồn tại trong thùng rác không
        $exists = TransactionCategory::onlyTrashed()->where('id', $transaction_category)->exists();
        if (!$exists) {
            return response()->json(['message' => __('messages.category.not_found_in_trash')], Response::HTTP_NOT_FOUND);
        }
        
        // Xóa hình ảnh trước
        $this->transactionCategoryService->removeImage($transaction_category);
        
        // Xóa vĩnh viễn danh mục
        $this->transactionCategoryService->forceDelete($transaction_category);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
 
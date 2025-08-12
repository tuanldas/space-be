<?php

namespace App\Http\Controllers\Api;

use App\Enums\AbilityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TransactionCategory\CreateTransactionCategoryRequest;
use App\Http\Requests\Api\TransactionCategory\UpdateTransactionCategoryRequest;
use App\Models\TransactionCategory;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TransactionCategoryController extends Controller
{
    /**
     * TransactionCategoryController constructor.
     */
    public function __construct(
        private TransactionCategoryServiceInterface $transactionCategoryService
    )
    {
    }

    /**
     * Kiểm tra quyền quản lý danh mục mặc định
     * 
     * @param TransactionCategory $category Danh mục cần kiểm tra
     * @param string $errorMessage Thông báo lỗi (key của file messages)
     * @return JsonResponse|null Trả về response lỗi hoặc null nếu có quyền
     */
    protected function checkDefaultCategoryPermission(TransactionCategory $category, string $errorMessage = 'messages.category.cannot_modify_default'): ?JsonResponse
    {
        if ($category->is_default && !Bouncer::can(AbilityType::MANAGE_DEFAULT_TRANSACTION_CATEGORIES->value)) {
            return response()->json([
                'message' => __($errorMessage)
            ], Response::HTTP_FORBIDDEN);
        }
        
        return null;
    }

    /**
     * Hiển thị danh sách danh mục giao dịch.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Hiển thị chi tiết một danh mục giao dịch.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $category = $this->transactionCategoryService->getById($id);
        return response()->json($category);
    }

    /**
     * Tạo mới danh mục giao dịch.
     *
     * @param CreateTransactionCategoryRequest $request
     * @return JsonResponse
     */
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

    /**
     * Cập nhật danh mục giao dịch.
     *
     * @param UpdateTransactionCategoryRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateTransactionCategoryRequest $request, string $id): JsonResponse
    {
        $category = $this->transactionCategoryService->getById($id);
        
        $permissionCheck = $this->checkDefaultCategoryPermission($category);
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
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

    /**
     * Xóa tạm danh mục giao dịch (soft delete).
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $category = $this->transactionCategoryService->getById($id);
        
        $permissionCheck = $this->checkDefaultCategoryPermission($category, 'messages.category.cannot_delete_default');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $this->transactionCategoryService->delete($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Hiển thị danh sách danh mục giao dịch đã xóa tạm.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trashed(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $userId = Auth::id();

        $trashedCategories = $this->transactionCategoryService->getTrashedByUser($userId, $perPage);

        return response()->json($trashedCategories);
    }

    /**
     * Khôi phục danh mục giao dịch đã xóa tạm.
     *
     * @param string $transaction_category
     * @return JsonResponse
     */
    public function restore(string $transaction_category): JsonResponse
    {
        $category = $this->transactionCategoryService->findTrashedByUuid($transaction_category);
        
        if ($category) {
            $permissionCheck = $this->checkDefaultCategoryPermission($category);
            if ($permissionCheck) {
                return $permissionCheck;
            }
        }
        
        $this->transactionCategoryService->restore($transaction_category);
        $category = $this->transactionCategoryService->getById($transaction_category);

        return response()->json($category);
    }

    /**
     * Xóa vĩnh viễn danh mục giao dịch.
     *
     * @param string $transaction_category
     * @return JsonResponse
     */
    public function forceDelete(string $transaction_category): JsonResponse
    {
        $category = TransactionCategory::onlyTrashed()->where('id', $transaction_category)->first();
        if (!$category) {
            return response()->json(['message' => __('messages.category.not_found_in_trash')], Response::HTTP_NOT_FOUND);
        }
        
        $permissionCheck = $this->checkDefaultCategoryPermission($category, 'messages.category.cannot_delete_default');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $this->transactionCategoryService->removeImage($transaction_category);
        $this->transactionCategoryService->forceDelete($transaction_category);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
 
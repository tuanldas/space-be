<?php

namespace App\Http\Requests\Api\TransactionCategory;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TransactionType;

class GetCategoryOptionsRequest extends FormRequest
{
    /**
     * Cho phép tất cả người dùng đã xác thực
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate tham số query cho API lấy options danh mục
     */
    public function rules(): array
    {
        return [
            'search' => 'sometimes|string',
            'type' => 'sometimes|string|in:' . TransactionType::getValuesString(),
            'limit' => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * Message tiếng Việt cho lỗi validate
     */
    public function messages(): array
    {
        return [
            'search.string' => __('validation.string'),
            'type.in' => __('validation.in'),
            'limit.integer' => __('validation.integer'),
            'limit.min' => __('validation.min.numeric', ['min' => 1]),
            'limit.max' => __('validation.max.numeric', ['max' => 100]),
        ];
    }
} 
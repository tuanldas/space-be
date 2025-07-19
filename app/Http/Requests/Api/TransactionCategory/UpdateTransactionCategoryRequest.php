<?php

namespace App\Http\Requests\Api\TransactionCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string|in:income,expense,transfer',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
} 
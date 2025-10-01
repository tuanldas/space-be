<?php

namespace App\Http\Requests\Api\Chart;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;

class GetTopCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => 'sometimes|date_format:Y-m',
            'wallet_id' => 'sometimes|uuid|exists:wallets,id',
            'limit' => 'sometimes|integer|min:1|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'month.date_format' => __('validation.date_format', ['format' => 'Y-m']),
            'wallet_id.uuid' => __('validation.uuid'),
            'wallet_id.exists' => __('validation.exists'),
            'limit.integer' => __('validation.integer'),
            'limit.min' => __('validation.min.numeric', ['min' => 1]),
            'limit.max' => __('validation.max.numeric', ['max' => 20]),
        ];
    }
}


<?php

namespace App\Http\Requests\Api\WalletTransaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TransactionType;

class IndexUserTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter.type' => 'sometimes|required|in:' . TransactionType::getValuesString(),
            'filter.date_between.start' => 'sometimes|required_with:filter.date_between.end|date_format:Y-m-d',
            'filter.date_between.end' => 'sometimes|required_with:filter.date_between.start|date_format:Y-m-d|after_or_equal:filter.date_between.start',
            // alias hỗ trợ
            'filter.date_from' => 'sometimes|date_format:Y-m-d',
            'filter.date_to' => 'sometimes|date_format:Y-m-d',
            'filter.wallet_id' => 'sometimes|string',
            'filter.category_id' => 'sometimes|string',
            'filter.category_ids' => 'sometimes|array',
            'filter.category_ids.*' => 'sometimes|string',
            'filter.search' => 'sometimes|string',
            'filter.min_amount' => 'sometimes|numeric',
            'filter.max_amount' => 'sometimes|numeric',
            'sort' => 'sometimes|string|in:transaction_date,-transaction_date,amount,-amount',
            'page' => 'sometimes|required|integer|min:1',
            'per_page' => 'sometimes|required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'filter.type.in' => __('validation.in'),
            'filter.type.required' => __('validation.required'),
            'filter.date_between.start.required_with' => __('validation.required_with', ['values' => 'end date']),
            'filter.date_between.start.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'filter.date_between.end.required_with' => __('validation.required_with', ['values' => 'start date']),
            'filter.date_between.end.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'filter.date_between.end.after_or_equal' => __('validation.after_or_equal', ['date' => 'start date']),
            'filter.date_from.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'filter.date_to.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'filter.min_amount.numeric' => __('validation.numeric'),
            'filter.max_amount.numeric' => __('validation.numeric'),
            'filter.category_ids.array' => __('validation.array'),
            'filter.category_ids.*.string' => __('validation.string'),
            'page.integer' => __('validation.integer'),
            'page.required' => __('validation.required'),
            'per_page.integer' => __('validation.integer'),
            'per_page.required' => __('validation.required'),
        ];
    }
} 
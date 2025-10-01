<?php

namespace App\Http\Requests\Api\Chart;

use Illuminate\Foundation\Http\FormRequest;

class GetMonthlyExpensesRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'month.date_format' => __('validation.date_format', ['format' => 'Y-m']),
            'wallet_id.uuid' => __('validation.uuid'),
            'wallet_id.exists' => __('validation.exists'),
        ];
    }
}


<?php

namespace App\Http\Requests\Api\WalletTransaction;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactionsByDateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => __('messages.validation.wallet_transaction.transaction_date_required'),
            'start_date.date' => __('messages.validation.wallet_transaction.transaction_date_date'),
            'end_date.required' => __('messages.validation.wallet_transaction.transaction_date_required'),
            'end_date.date' => __('messages.validation.wallet_transaction.transaction_date_date'),
            'end_date.after_or_equal' => __('validation.after_or_equal'),
        ];
    }
} 
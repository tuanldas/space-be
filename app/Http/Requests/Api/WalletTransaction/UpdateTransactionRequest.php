<?php

namespace App\Http\Requests\Api\WalletTransaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TransactionType;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|required|numeric|gt:0',
            'transaction_type' => 'sometimes|required|string|in:' . TransactionType::getValuesString(),
            'category_id' => 'sometimes|required|uuid|exists:transaction_categories,id',
            'description' => 'sometimes|nullable|string',
            'transaction_date' => 'prohibited',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => __('messages.validation.wallet_transaction.amount_required'),
            'amount.numeric' => __('messages.validation.wallet_transaction.amount_numeric'),
            'amount.gt' => __('messages.validation.wallet_transaction.amount_min'),

            'transaction_type.required' => __('messages.validation.wallet_transaction.transaction_type_required'),
            'transaction_type.string' => __('messages.validation.wallet_transaction.transaction_type_string'),
            'transaction_type.in' => __('messages.validation.wallet_transaction.transaction_type_in'),

            'category_id.required' => __('messages.validation.wallet_transaction.category_id_required'),
            'category_id.uuid' => __('messages.validation.wallet_transaction.category_id_uuid'),
            'category_id.exists' => __('messages.validation.wallet_transaction.category_id_exists'),

            'description.string' => __('messages.validation.wallet_transaction.description_string'),

            'transaction_date.prohibited' => __('messages.validation.wallet_transaction.transaction_date_prohibited'),
        ];
    }
} 
<?php

namespace App\Http\Requests\Api\WalletTransaction;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wallet_id' => 'required|uuid|exists:wallets,id',
            'category_id' => 'required|uuid|exists:transaction_categories,id',
            'amount' => 'required|numeric|gt:0',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|string|in:' . TransactionType::getValuesString(),
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wallet_id.required' => __('messages.validation.wallet_transaction.wallet_id_required'),
            'wallet_id.uuid' => __('messages.validation.wallet_transaction.wallet_id_uuid'),
            'wallet_id.exists' => __('messages.validation.wallet_transaction.wallet_id_exists'),
            'category_id.required' => __('messages.validation.wallet_transaction.category_id_required'),
            'category_id.uuid' => __('messages.validation.wallet_transaction.category_id_uuid'),
            'category_id.exists' => __('messages.validation.wallet_transaction.category_id_exists'),
            'amount.required' => __('messages.validation.wallet_transaction.amount_required'),
            'amount.numeric' => __('messages.validation.wallet_transaction.amount_numeric'),
            'amount.gt' => __('messages.validation.wallet_transaction.amount_min'),
            'transaction_date.required' => __('messages.validation.wallet_transaction.transaction_date_required'),
            'transaction_date.date' => __('messages.validation.wallet_transaction.transaction_date_date'),
            'transaction_type.required' => __('messages.validation.wallet_transaction.transaction_type_required'),
            'transaction_type.string' => __('messages.validation.wallet_transaction.transaction_type_string'),
            'transaction_type.in' => __('messages.validation.wallet_transaction.transaction_type_in'),
            'description.string' => __('messages.validation.wallet_transaction.description_string'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
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
            'name.required' => __('messages.validation.wallet.name_required'),
            'name.string' => __('messages.validation.wallet.name_string'),
            'name.max' => __('messages.validation.wallet.name_max'),
            'balance.numeric' => __('messages.validation.wallet.balance_numeric'),
            'balance.min' => __('messages.validation.wallet.balance_min'),
            'currency.required' => __('messages.validation.wallet.currency_required'),
            'currency.string' => __('messages.validation.wallet.currency_string'),
            'currency.size' => __('messages.validation.wallet.currency_size'),
        ];
    }
}

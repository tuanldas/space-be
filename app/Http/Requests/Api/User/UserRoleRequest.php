<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => __('validation.required', ['attribute' => 'ID người dùng']),
            'user_id.exists' => __('validation.exists', ['attribute' => 'người dùng']),
            'role.required' => __('validation.required', ['attribute' => 'tên vai trò']),
            'role.exists' => __('validation.exists', ['attribute' => 'vai trò']),
        ];
    }
}

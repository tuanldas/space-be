<?php

namespace App\Http\Requests\Api\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            'name' => 'nullable|string|max:255|unique:roles,name,' . $this->route('role'),
            'title' => 'nullable|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'exists:abilities,name',
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
            'name.string' => __('validation.string', ['attribute' => 'tên vai trò']),
            'name.max' => __('validation.max.string', ['attribute' => 'tên vai trò', 'max' => 255]),
            'name.unique' => __('validation.unique', ['attribute' => 'tên vai trò']),
            'title.string' => __('validation.string', ['attribute' => 'tiêu đề vai trò']),
            'title.max' => __('validation.max.string', ['attribute' => 'tiêu đề vai trò', 'max' => 255]),
            'abilities.array' => __('validation.array', ['attribute' => 'danh sách quyền']),
            'abilities.*.exists' => __('validation.exists', ['attribute' => 'quyền đã chọn']),
        ];
    }
} 
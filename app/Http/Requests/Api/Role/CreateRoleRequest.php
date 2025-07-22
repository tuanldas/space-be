<?php

namespace App\Http\Requests\Api\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Bouncer;

class CreateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Bouncer::can('manage-roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'title' => 'nullable|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'exists:abilities,name',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('Bạn không có quyền quản lý vai trò.');
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Tên vai trò là bắt buộc.',
            'name.string' => 'Tên vai trò phải là chuỗi.',
            'name.max' => 'Tên vai trò không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên vai trò đã tồn tại.',
            'title.string' => 'Tiêu đề vai trò phải là chuỗi.',
            'title.max' => 'Tiêu đề vai trò không được vượt quá 255 ký tự.',
            'abilities.array' => 'Danh sách quyền phải là một mảng.',
            'abilities.*.exists' => 'Một số quyền được chọn không tồn tại.',
        ];
    }
} 
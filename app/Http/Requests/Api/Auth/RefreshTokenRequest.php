<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => 'sometimes|string',
        ];
    }
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Nếu refresh_token không được cung cấp trong request nhưng có trong cookie
        if (!$this->has('refresh_token') && $this->cookie('refresh_token')) {
            $this->merge([
                'refresh_token' => $this->cookie('refresh_token')
            ]);
        }
    }
} 
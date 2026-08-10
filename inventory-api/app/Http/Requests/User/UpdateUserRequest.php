<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:200',
                Rule::unique('users', 'name')->ignore($userId),
            ],
            'username' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'employee_id' => [
                'sometimes',
                'integer',
                Rule::unique('users', 'employee_id')->ignore($userId),
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

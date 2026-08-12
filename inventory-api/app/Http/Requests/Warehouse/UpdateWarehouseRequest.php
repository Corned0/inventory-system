<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
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
        $warehouse = $this->route('warehouse');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('warehouses', 'code')
                    ->ignore($warehouse->id),
            ],
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'address' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

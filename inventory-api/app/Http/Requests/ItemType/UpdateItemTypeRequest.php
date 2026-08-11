<?php

namespace App\Http\Requests\ItemType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemTypeRequest extends FormRequest
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
        $itemType = $this->route('itemType');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('item_types', 'code')
                    ->ignore($itemType->id),
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
            'tracking_type' => [
                'sometimes',
                Rule::in([
                    'none',
                    'lot',
                    'serial',
                ]),
            ],
            'is_asset' => [
                'sometimes',
                'boolean',
            ],
            'is_composite' => [
                'sometimes',
                'boolean',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

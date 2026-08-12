<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
        $item = $this->route('item');

        return [
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

            'barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'barcode')
                    ->ignore($item?->id),
            ],

            'category_id' => [
                'sometimes',
                'integer',
                'exists:item_categories,id',
            ],

            'item_type_id' => [
                'sometimes',
                'integer',
                'exists:item_types,id',
            ],

            'unit_of_measure_id' => [
                'sometimes',
                'integer',
                'exists:units_of_measure,id',
            ],

            'reorder_level' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'reorder_quantity' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'attributes' => [
                'sometimes',
                'array',
            ],
        ];
    }
}

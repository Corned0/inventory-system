<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'barcode'),
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:item_categories,id',
            ],

            'item_type_id' => [
                'required',
                'integer',
                'exists:item_types,id',
            ],

            'unit_of_measure_id' => [
                'required',
                'integer',
                'exists:units_of_measure,id',
            ],

            'reorder_level' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'reorder_quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'attributes' => [
                'sometimes',
                'array',
            ],
        ];
    }
}

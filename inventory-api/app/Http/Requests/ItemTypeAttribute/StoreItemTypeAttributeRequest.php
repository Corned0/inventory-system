<?php

namespace App\Http\Requests\ItemTypeAttribute;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemTypeAttributeRequest extends FormRequest
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

        $itemTypeId = $itemType?->id ?? $itemType;

        return [
            'attribute_definition_id' => [
                'required',
                'integer',
                'exists:attribute_definitions,id',
                Rule::unique('item_type_attributes', 'attribute_definition_id')
                    ->where('item_type_id', $itemTypeId),
            ],

            'is_required' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }
}

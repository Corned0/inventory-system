<?php

namespace App\Http\Requests\AttributeOption;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeOptionRequest extends FormRequest
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
        $option = $this->route('option');

        return [
            'value' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('attribute_options', 'value')
                    ->where(
                        'attribute_definition_id',
                        $option->attribute_definition_id
                    )
                    ->ignore($option->id),
            ],

            'label' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}

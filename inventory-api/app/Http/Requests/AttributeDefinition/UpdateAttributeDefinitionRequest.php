<?php

namespace App\Http\Requests\AttributeDefinition;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $attributeDefinition = $this->route('attribute');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('attribute_definitions', 'code')
                    ->ignore($attributeDefinition->id),
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'data_type' => [
                'sometimes',
                Rule::in([
                    'text',
                    'textarea',
                    'integer',
                    'decimal',
                    'boolean',
                    'date',
                    'datetime',
                    'select',
                    'multiselect',
                ]),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'is_required' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'validation_rules' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'default_value' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
    }
}
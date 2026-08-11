<?php

namespace App\Http\Requests\AttributeDefinition;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeDefinitionRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('attribute_definitions', 'code'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'data_type' => [
                'required',
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
                'nullable',
                'string',
            ],

            'is_required' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'integer',
                'min:0',
            ],

            'validation_rules' => [
                'nullable',
                'array',
            ],

            'default_value' => [
                'nullable',
                'string',
            ],
        ];
    }
}

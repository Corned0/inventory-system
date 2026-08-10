<?php

namespace App\Http\Requests\UnitOfMeasure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'uppercase',
                Rule::unique('units_of_measure', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'symbol' => [
                'required',
                'string',
                'max:20',
            ],
            'decimal_places' => [
                'required',
                'integer',
                'min:0',
                'max:6',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
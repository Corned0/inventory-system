<?php

namespace App\Http\Requests\UnitOfMeasure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitOfMeasureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unit = $this->route('unit');

        $unitId = $unit instanceof \App\Models\UnitOfMeasure
            ? $unit->id
            : $unit;

        return [
            'code' => [
                'sometimes',
                'string',
                'max:20',
                'uppercase',
                Rule::unique('units_of_measure', 'code')
                    ->ignore($unitId),
            ],
            'name' => [
                'sometimes',
                'string',
                'max:100',
            ],
            'symbol' => [
                'sometimes',
                'string',
                'max:20',
            ],
            'decimal_places' => [
                'sometimes',
                'integer',
                'min:0',
                'max:6',
            ]/* ,
            'is_active' => [
                'sometimes',
                'boolean',
            ], */
        ];
    }
}
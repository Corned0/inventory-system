<?php

namespace App\Http\Requests\Location;

use App\Models\Location;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    private const LOCATION_TYPES = [
        'building',
        'room',
        'rack',
        'shelf',
        'bin',
    ];

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
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'parent_id' => [
                Rule::requiredIf(
                    fn () => $this->input('location_type') !== 'building'
                ),
                'nullable',
                'integer',
                'exists:locations,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'location_type' => [
                'required',
                Rule::in(self::LOCATION_TYPES),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $parentId = $this->input('parent_id');

            if ($parentId === null) {
                return;
            }

            $parent = Location::find($parentId);

            if (!$parent) {
                return;
            }

            $warehouseId = (int) $this->input('warehouse_id');

            if ($parent->warehouse_id !== $warehouseId) {
                $validator->errors()->add(
                    'parent_id',
                    'The parent location must belong to the same warehouse.'
                );

                return;
            }

            $expectedParentType = $this->expectedParentType(
                $this->input('location_type')
            );

            if ($parent->location_type !== $expectedParentType) {
                $validator->errors()->add(
                    'parent_id',
                    "A {$this->input('location_type')} location must have a {$expectedParentType} parent."
                );
            }
        });
    }

    private function expectedParentType(string $locationType): string
    {
        return match ($locationType) {
            'room' => 'building',
            'rack' => 'room',
            'shelf' => 'rack',
            'bin' => 'shelf',
            default => '',
        };
    }
}

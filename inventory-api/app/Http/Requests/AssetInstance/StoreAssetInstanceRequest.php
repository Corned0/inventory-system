<?php

namespace App\Http\Requests\AssetInstance;

use App\Enums\AssetInstanceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetInstanceRequest extends FormRequest
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
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'serial_number' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique(
                    'asset_instances',
                    'serial_number'
                ),
            ],

            'status' => [
                'sometimes',
                Rule::enum(AssetInstanceStatus::class),
            ],

            'current_warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],

            'current_location_id' => [
                'nullable',
                'integer',
                'exists:locations,id',
            ],

            'acquired_at' => [
                'nullable',
                'date',
            ],

            'acquisition_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'warranty_start' => [
                'nullable',
                'date',
            ],

            'warranty_end' => [
                'nullable',
                'date',
                'after_or_equal:warranty_start',
            ],
        ];
    }
}

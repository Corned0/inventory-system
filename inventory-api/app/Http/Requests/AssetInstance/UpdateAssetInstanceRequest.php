<?php

namespace App\Http\Requests\AssetInstance;

use App\Enums\AssetInstanceStatus;
use App\Models\AssetInstance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetInstanceRequest extends FormRequest
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
        /** @var AssetInstance $assetInstance */
        $assetInstance = $this->route('assetInstance');

        return [
            'item_id' => [
                'sometimes',
                'integer',
                'exists:items,id',
            ],

            'serial_number' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique(
                    'asset_instances',
                    'serial_number'
                )->ignore($assetInstance->id),
            ],

            'status' => [
                'sometimes',
                Rule::enum(AssetInstanceStatus::class),
            ],

            'current_warehouse_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],

            'current_location_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:locations,id',
            ],

            'acquired_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'acquisition_cost' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'warranty_start' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'warranty_end' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:warranty_start',
            ],
        ];
    }
}

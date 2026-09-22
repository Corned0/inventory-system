<?php

namespace App\Http\Requests\InventorySerial;

use App\Enums\InventorySerialStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventorySerialRequest extends FormRequest
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
        $serial = $this->route('inventorySerial') ?? $this->route('inventory_serial');

        return [
            'item_id' => [
                'prohibited',
            ],

            'serial_number' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('inventory_serials', 'serial_number')
                    ->ignore($serial?->id),
            ],

            'status' => [
               'prohibited',
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
        ];
    }
}

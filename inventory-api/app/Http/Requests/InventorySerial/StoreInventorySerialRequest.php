<?php

namespace App\Http\Requests\InventorySerial;

use App\Enums\InventorySerialStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventorySerialRequest extends FormRequest
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
                Rule::unique('inventory_serials', 'serial_number')
                    ->where(fn ($query) => $query->where(
                        'item_id',
                        $this->input('item_id')
                    )),
            ],

            'status' => [
                'nullable',
                'string',
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
        ];
    }
}

<?php

namespace App\Http\Requests\InventoryTransaction;

use App\Models\InventoryTransaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryTransactionRequest extends FormRequest
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
            'transaction_type' => [
                'required',
                'string',
                Rule::in(InventoryTransaction::TYPES),
            ],

            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'location_id' => [
                'nullable',
                'integer',
                'exists:locations,id',
            ],

            'lot_id' => [
                'nullable',
                'integer',
                'exists:inventory_lots,id',
            ],

            'serial_id' => [
                'nullable',
                'integer',
                'exists:inventory_serials,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'reference_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'reference_id' => [
                'nullable',
                'integer',
            ],

            'transaction_date' => [
                'sometimes',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ];
    }
}

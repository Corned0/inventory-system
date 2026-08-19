<?php

namespace App\Http\Requests\Receiving;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceivingRequest extends FormRequest
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
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'purchase_order_id' => [
                'nullable',
                'integer',
            ],

            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'received_date' => [
                'nullable',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],

            'items.*.ordered_quantity' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'items.*.received_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.accepted_quantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.rejected_quantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Receiving;

use App\Models\Receiving;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceivingRequest extends FormRequest
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
        /** @var Receiving|null $receiving */
        $receiving = $this->route('receiving');

        return [
            'supplier_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'purchase_order_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:purchase_orders,id',
            ],

            'warehouse_id' => [
                'sometimes',
                'integer',
                'exists:warehouses,id',
            ],

            'received_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'remarks' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'sometimes',
                'array',
                'min:1',
            ],

            'items.*.item_id' => [
                'required_with:items',
                'integer',
                'exists:items,id',
            ],

            'items.*.ordered_quantity' => [
                'required_with:items',
                'numeric',
                'gt:0',
            ],

            'items.*.received_quantity' => [
                'required_with:items',
                'numeric',
                'gte:0',
            ],

            'items.*.unit_cost' => [
                'sometimes',
                'nullable',
                'numeric',
                'gte:0',
            ],
        ];
    }
}

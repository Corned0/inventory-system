<?php

namespace App\Http\Requests\InventoryTransaction;

use App\Enums\InventoryTransactionType;
use App\Services\Inventory\InventoryTrackingService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

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
                Rule::enum(InventoryTransactionType::class),
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
                'max:1000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $itemId = $this->input('item_id');
                $lotId = $this->input('lot_id');
                $serialId = $this->input('serial_id');
                $warehouseId = $this->input('warehouse_id');
                $locationId = $this->input('location_id');

                if ($itemId === null) {
                    return;
                }

                $item = \App\Models\Item::query()->with('itemType')->find($itemId);

                if ($item === null) {
                    return;
                }

                try {
                    app(InventoryTrackingService::class)->validateForTransaction(
                        item: $item,
                        lotId: $lotId !== null ? (int) $lotId : null,
                        serialId: $serialId !== null ? (int) $serialId : null,
                        quantity: $this->input('quantity'),
                        warehouseId: $warehouseId !== null ? (int) $warehouseId : null,
                        locationId: $locationId !== null ? (int) $locationId : null,
                    );
                } catch (InvalidArgumentException $exception) {
                    $validator->errors()->add('tracking', $exception->getMessage());
                }
            },
        ];
    }
}

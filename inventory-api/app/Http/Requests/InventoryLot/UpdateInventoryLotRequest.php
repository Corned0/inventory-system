<?php

namespace App\Http\Requests\InventoryLot;

use App\Models\InventoryLot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryLotRequest extends FormRequest
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
        $lot = $this->route('inventory_lot');

        $itemId = $this->input(
            'item_id',
            $lot->item_id
        );

        return [
            'item_id' => [
                'sometimes',
                'integer',
                'exists:items,id',
            ],

            'lot_number' => [
                'sometimes',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('inventory_lots', 'lot_number')
                    ->where(
                        fn ($query) => $query->where(
                            'item_id',
                            $itemId
                        )
                    )
                    ->ignore($lot->id),
            ],

            'manufactured_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'expiration_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:manufactured_date',
            ],
        ];
    }
}

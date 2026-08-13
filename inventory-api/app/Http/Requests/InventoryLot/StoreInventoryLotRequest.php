<?php

namespace App\Http\Requests\InventoryLot;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryLotRequest extends FormRequest
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

            'lot_number' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('inventory_lots', 'lot_number')
                    ->where(
                        fn ($query) => $query->where(
                            'item_id',
                            $this->input('item_id')
                        )
                    ),
            ],

            'manufactured_date' => [
                'nullable',
                'date',
            ],

            'expiration_date' => [
                'nullable',
                'date',
                'after_or_equal:manufactured_date',
            ],
        ];
    }
}

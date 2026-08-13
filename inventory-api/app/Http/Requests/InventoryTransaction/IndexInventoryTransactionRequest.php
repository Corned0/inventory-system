<?php

namespace App\Http\Requests\InventoryTransaction;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexInventoryTransactionRequest extends FormRequest
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
            'item_id' => ['sometimes', 'integer', 'exists:items,id'],
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'transaction_type' => [
                'sometimes',
                'string',
                Rule::in([
                    'receipt',
                    'put_away',
                    'issue',
                    'transfer_out',
                    'transfer_in',
                    'return',
                    'adjustment_in',
                    'adjustment_out',
                    'disposal',
                    'assembly',
                    'disassembly',
                    'stock_count',
                ]),
            ],
        ];
    }
}

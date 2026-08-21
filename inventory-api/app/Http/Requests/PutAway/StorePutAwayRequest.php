<?php

namespace App\Http\Requests\PutAway;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePutAwayRequest extends FormRequest
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
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.receiving_item_id' => [
                'required',
                'integer',
                'exists:receiving_items,id',
            ],

            'items.*.location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'items.*.lot_id' => [
                'nullable',
                'integer',
                'exists:inventory_lots,id',
            ],

            'items.*.serial_id' => [
                'nullable',
                'integer',
                'exists:inventory_serials,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }
}

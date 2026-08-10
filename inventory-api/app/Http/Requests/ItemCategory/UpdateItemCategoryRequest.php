<?php

namespace App\Http\Requests\ItemCategory;

use App\Models\ItemCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateItemCategoryRequest extends FormRequest
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
        $category = $this->route('category');

        $categoryId = $category?->id ?? $category;

        return [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:item_categories,id',
                Rule::notIn([$categoryId]),
            ],
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('item_categories', 'code')
                    ->ignore($categoryId),
            ],
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('parent_id')) {
                    return;
                }

                $category = $this->route('category');

                if (! $category instanceof ItemCategory) {
                    return;
                }

                $parentId = (int) $this->input('parent_id');

                if ($category->isAncestorOf($parentId)) {
                    $validator->errors()->add(
                        'parent_id',
                        'A category cannot have one of its descendants as its parent.'
                    );
                }
            },
        ];
    }
}

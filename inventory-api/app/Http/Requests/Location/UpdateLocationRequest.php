<?php

namespace App\Http\Requests\Location;

use App\Models\Location;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    private const LOCATION_TYPES = [
        'building',
        'room',
        'rack',
        'shelf',
        'bin',
    ];
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
        /** @var Location $location */
        $location = $this->route('location');

        return [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:locations,id',
            ],

            'code' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'location_type' => [
                'sometimes',
                Rule::in(self::LOCATION_TYPES),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Location $location */
            $location = $this->route('location');

            if (!$this->has('parent_id')) {
                return;
            }

            $parentId = $this->input('parent_id');

            $locationType = $this->input(
                'location_type',
                $location->location_type
            );

            if ($parentId === null) {
                if ($locationType !== 'building') {
                    $validator->errors()->add(
                        'parent_id',
                        "A {$locationType} location requires a parent location."
                    );
                }

                return;
            }

            if ((int) $parentId === $location->id) {
                $validator->errors()->add(
                    'parent_id',
                    'A location cannot be its own parent.'
                );

                return;
            }

            $parent = Location::find($parentId);

            if (!$parent) {
                return;
            }

            if ($parent->warehouse_id !== $location->warehouse_id) {
                $validator->errors()->add(
                    'parent_id',
                    'The parent location must belong to the same warehouse.'
                );

                return;
            }

            if ($this->isDescendant($location, $parent)) {
                $validator->errors()->add(
                    'parent_id',
                    'A location cannot be moved under one of its descendants.'
                );

                return;
            }

            $expectedParentType = $this->expectedParentType($locationType);

            if ($parent->location_type !== $expectedParentType) {
                $validator->errors()->add(
                    'parent_id',
                    "A {$locationType} location must have a {$expectedParentType} parent."
                );
            }
        });
    }

    private function isDescendant(
        Location $location,
        Location $candidate
    ): bool {
        $current = $candidate;

        while ($current->parent_id !== null) {
            if ($current->parent_id === $location->id) {
                return true;
            }

            $current = $current->parent;

            if (!$current) {
                break;
            }
        }

        return false;
    }

    private function expectedParentType(string $locationType): string
    {
        return match ($locationType) {
            'room' => 'building',
            'rack' => 'room',
            'shelf' => 'rack',
            'bin' => 'shelf',
            default => '',
        };
    }
}

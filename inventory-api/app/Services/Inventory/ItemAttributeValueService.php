<?php

namespace App\Services\Inventory;

use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
use App\Models\Item;
use App\Models\ItemType;
use Illuminate\Validation\ValidationException;

class ItemAttributeValueService
{
    /**
     * Validate and persist dynamic attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function sync(
        Item $item,
        array $attributes,
    ): void {
        $item->loadMissing([
            'itemType.attributeDefinitions.options',
        ]);

        $definitions = $item->itemType
            ->attributeDefinitions
            ->keyBy('code');

        $this->validateUnknownAttributes(
            $attributes,
            $definitions
        );

        $this->validateRequiredAttributes(
            $attributes,
            $definitions
        );

        $validatedValues = [];

        foreach ($attributes as $code => $value) {
            /** @var AttributeDefinition $definition */
            $definition = $definitions->get($code);

            $this->validateValue(
                $definition,
                $value
            );

            $validatedValues[$definition->id] = $this->normalizeValue(
                $definition,
                $value
            );
        }

        $item->attributeValues()->delete();

        foreach ($validatedValues as $attributeDefinitionId => $value) {
            $item->attributeValues()->create([
                'attribute_definition_id' => $attributeDefinitionId,
                'value' => $value,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $attributes
     * @param \Illuminate\Support\Collection<string, AttributeDefinition> $definitions
     */
    private function validateUnknownAttributes(
        array $attributes,
        $definitions,
    ): void {
        $unknown = array_diff(
            array_keys($attributes),
            $definitions->keys()->all()
        );

        if ($unknown === []) {
            return;
        }

        throw ValidationException::withMessages([
            'attributes' => [
                'Unknown attributes: ' . implode(', ', $unknown),
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param \Illuminate\Support\Collection<string, AttributeDefinition> $definitions
     */
    private function validateRequiredAttributes(
        array $attributes,
        $definitions,
    ): void {
        foreach ($definitions as $definition) {
            if (
                !$definition->pivot->is_required &&
                !$definition->is_required
            ) {
                continue;
            }

            if (
                !array_key_exists($definition->code, $attributes) ||
                $this->isEmpty($attributes[$definition->code])
            ) {
                throw ValidationException::withMessages([
                    "attributes.{$definition->code}" => [
                        "The {$definition->name} field is required.",
                    ],
                ]);
            }
        }
    }

    private function validateValue(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if ($value === null) {
            if ($definition->is_required) {
                throw ValidationException::withMessages([
                    "attributes.{$definition->code}" => [
                        "The {$definition->name} field is required.",
                    ],
                ]);
            }

            return;
        }

        match ($definition->data_type) {
            'text', 'textarea' => $this->validateText(
                $definition,
                $value
            ),

            'integer' => $this->validateInteger(
                $definition,
                $value
            ),

            'decimal' => $this->validateDecimal(
                $definition,
                $value
            ),

            'boolean' => $this->validateBoolean(
                $definition,
                $value
            ),

            'date', 'datetime' => $this->validateDate(
                $definition,
                $value
            ),

            'select' => $this->validateSelect(
                $definition,
                $value
            ),

            'multiselect' => $this->validateMultiselect(
                $definition,
                $value
            ),

            default => throw ValidationException::withMessages([
                "attributes.{$definition->code}" => [
                    'Unsupported attribute data type.',
                ],
            ]),
        };
    }

    private function validateText(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_string($value)) {
            $this->invalid($definition);
        }
    }

    private function validateInteger(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_int($value)) {
            $this->invalid($definition);
        }
    }

    private function validateDecimal(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_numeric($value)) {
            $this->invalid($definition);
        }
    }

    private function validateBoolean(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_bool($value)) {
            $this->invalid($definition);
        }
    }

    private function validateDate(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_string($value) || strtotime($value) === false) {
            $this->invalid($definition);
        }
    }

    private function validateSelect(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_string($value)) {
            $this->invalid($definition);

            return;
        }

        $exists = $definition->options
            ->where('is_active', true)
            ->contains('value', $value);

        if (!$exists) {
            throw ValidationException::withMessages([
                "attributes.{$definition->code}" => [
                    'The selected option is invalid.',
                ],
            ]);
        }
    }

    private function validateMultiselect(
        AttributeDefinition $definition,
        mixed $value,
    ): void {
        if (!is_array($value)) {
            $this->invalid($definition);

            return;
        }

        $validOptions = $definition->options
            ->where('is_active', true)
            ->pluck('value');

        foreach ($value as $selected) {
            if (!is_string($selected) || !$validOptions->contains($selected)) {
                throw ValidationException::withMessages([
                    "attributes.{$definition->code}" => [
                        'One or more selected options are invalid.',
                    ],
                ]);
            }
        }
    }

    private function normalizeValue(
        AttributeDefinition $definition,
        mixed $value,
    ): string {
        return match ($definition->data_type) {
            'multiselect' => json_encode(
                $value,
                JSON_THROW_ON_ERROR
            ),

            'boolean' => $value ? '1' : '0',

            default => (string) $value,
        };
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private function invalid(AttributeDefinition $definition): never
    {
        throw ValidationException::withMessages([
            "attributes.{$definition->code}" => [
                "The {$definition->name} value is invalid.",
            ],
        ]);
    }
}
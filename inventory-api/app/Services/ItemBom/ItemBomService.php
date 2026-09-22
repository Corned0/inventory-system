<?php

namespace App\Services\ItemBom;

use App\Exceptions\ActiveBomExistsException;
use App\Exceptions\BomCircularReferenceException;
use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemBomComponent;
use Illuminate\Support\Facades\DB;

class ItemBomService
{
    public function create(
        Item $item,
        array $data
    ): ItemBom {
        return DB::transaction(function () use ($item, $data): ItemBom {
            $this->assertItemIsComposite($item);

            $isActive = $data['is_active'] ?? true;
            $version = (int) $data['version'];

            if ($item->boms()->where('version', $version)->exists()) {
                throw new \InvalidArgumentException('A BOM version already exists for this item.');
            }

            if (
                $isActive &&
                $item->boms()
                    ->where('is_active', true)
                    ->exists()
            ) {
                throw new ActiveBomExistsException();
            }

            return ItemBom::create([
                'item_id' => $item->id,
                'name' => $data['name'],
                'version' => $version,
                'is_active' => $isActive,
            ]);
        });
    }

    public function update(
        ItemBom $bom,
        array $data
    ): ItemBom {
        return DB::transaction(function () use ($bom, $data): ItemBom {
            $isActive = $data['is_active'] ?? $bom->is_active;

            if (isset($data['version'])) {
                $version = (int) $data['version'];

                if ($bom->item->boms()->where('version', $version)->whereKeyNot($bom->id)->exists()) {
                    throw new \InvalidArgumentException('A BOM version already exists for this item.');
                }
            }

            if (
                $isActive &&
                ! $bom->is_active &&
                $bom->item->boms()
                    ->where('is_active', true)
                    ->whereKeyNot($bom->id)
                    ->exists()
            ) {
                throw new ActiveBomExistsException();
            }

            $bom->update($data);

            return $bom->refresh();
        });
    }

    public function addComponent(
        ItemBom $bom,
        array $data
    ): ItemBomComponent {
        $componentItemId = (int) $data['component_item_id'];

        $this->assertComponentIsValid($bom, $componentItemId, null);

        return $bom->components()->create([
            'component_item_id' => $componentItemId,
            'quantity' => $data['quantity'],
            'is_required' => $data['is_required'] ?? true,
        ]);
    }

    public function updateComponent(
        ItemBomComponent $component,
        array $data
    ): ItemBomComponent {
        $componentItemId = $data['component_item_id']
            ?? $component->component_item_id;

        $this->assertComponentIsValid(
            $component->bom,
            (int) $componentItemId,
            $component->id
        );

        $component->update($data);

        return $component->refresh();
    }

    public function deleteComponent(
        ItemBomComponent $component
    ): void {
        $component->delete();
    }

    private function assertItemIsComposite(Item $item): void
    {
        if (! (bool) $item->itemType?->is_composite) {
            throw new \InvalidArgumentException('Only composite items can have BOMs.');
        }
    }

    private function assertComponentIsValid(
        ItemBom $bom,
        int $componentItemId,
        ?int $excludeComponentId = null
    ): void {
        if ($componentItemId === (int) $bom->item_id) {
            throw new BomCircularReferenceException();
        }

        $duplicateExists = $bom->components()
            ->where('component_item_id', $componentItemId)
            ->when(
                $excludeComponentId !== null,
                fn ($query) => $query->whereKeyNot($excludeComponentId)
            )
            ->exists();

        if ($duplicateExists) {
            throw new \InvalidArgumentException('This component already exists on the BOM.');
        }

        $this->assertNoCircularReference($bom, $componentItemId);
    }

    private function assertNoCircularReference(
        ItemBom $bom,
        int $componentItemId
    ): void {
        $visited = [];
        $stack = [$componentItemId];

        while ($stack !== []) {
            $currentId = array_pop($stack);

            if (in_array($currentId, $visited, true)) {
                continue;
            }

            $visited[] = $currentId;

            $next = ItemBom::query()
                ->where('item_id', $currentId)
                ->with('components.componentItem')
                ->first();

            if ($next === null) {
                continue;
            }

            foreach ($next->components as $component) {
                $childId = (int) $component->component_item_id;

                if ($childId === (int) $bom->item_id) {
                    throw new BomCircularReferenceException();
                }

                if ($childId !== $currentId && ! in_array($childId, $visited, true)) {
                    $stack[] = $childId;
                }
            }
        }
    }
}
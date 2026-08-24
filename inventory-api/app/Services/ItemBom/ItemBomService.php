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
            $isActive = $data['is_active'] ?? true;

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
                'version' => $data['version'],
                'is_active' => $isActive,
            ]);
        });
    }

    public function update(
        ItemBom $bom,
        array $data
    ): ItemBom {
        return DB::transaction(function () use ($bom, $data): ItemBom {
            $isActive = $data['is_active']
                ?? $bom->is_active;

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
        if (
            (int) $data['component_item_id'] ===
            (int) $bom->item_id
        ) {
            throw new BomCircularReferenceException();
        }

        return $bom->components()->create([
            'component_item_id' => $data['component_item_id'],
            'quantity' => $data['quantity'],
            'is_required' => $data['is_required'] ?? true,
        ]);
    }

    public function updateComponent(
        ItemBomComponent $component,
        array $data
    ): ItemBomComponent {
        if (
            isset($data['component_item_id']) &&
            (int) $data['component_item_id'] ===
            (int) $component->bom->item_id
        ) {
            throw new BomCircularReferenceException();
        }

        $component->update($data);

        return $component->refresh();
    }

    public function deleteComponent(
        ItemBomComponent $component
    ): void {
        $component->delete();
    }
}
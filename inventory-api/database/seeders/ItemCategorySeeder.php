<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemCategory;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itEquipment = ItemCategory::updateOrCreate(
            ['code' => 'IT'],
            [
                'name' => 'IT Equipment',
                'description' => 'Information technology equipment.',
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'IT-COMPUTERS'],
            [
                'parent_id' => $itEquipment->id,
                'name' => 'Computers',
                'description' => 'Desktop and laptop computers.',
                'is_active' => true,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'IT-PRINTERS'],
            [
                'parent_id' => $itEquipment->id,
                'name' => 'Printers',
                'description' => 'Printers and printing equipment.',
                'is_active' => true,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'IT-NETWORKING'],
            [
                'parent_id' => $itEquipment->id,
                'name' => 'Networking',
                'description' => 'Network equipment.',
                'is_active' => true,
            ]
        );

        $officeSupplies = ItemCategory::updateOrCreate(
            ['code' => 'OFFICE'],
            [
                'name' => 'Office Supplies',
                'description' => 'General office supplies.',
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'OFFICE-PAPER'],
            [
                'parent_id' => $officeSupplies->id,
                'name' => 'Paper',
                'description' => 'Paper products.',
                'is_active' => true,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'OFFICE-WRITING'],
            [
                'parent_id' => $officeSupplies->id,
                'name' => 'Writing Materials',
                'description' => 'Pens, pencils, markers, and similar supplies.',
                'is_active' => true,
            ]
        );

        ItemCategory::updateOrCreate(
            ['code' => 'OFFICE-FOLDERS'],
            [
                'parent_id' => $officeSupplies->id,
                'name' => 'Folders',
                'description' => 'Folders and filing supplies.',
                'is_active' => true,
            ]
        );
    }
}

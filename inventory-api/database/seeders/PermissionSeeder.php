<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Users
            ['name' => 'users.view',   'display_name' => 'View Users',   'module' => 'Users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'Users'],
            ['name' => 'users.update', 'display_name' => 'Update Users', 'module' => 'Users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'Users'],

            // Permissions
            ['name' => 'permissions.view',   'display_name' => 'View Permissions',   'module' => 'Permissions'],
            ['name' => 'permissions.create', 'display_name' => 'Create Permissions', 'module' => 'Permissions'],
            ['name' => 'permissions.update', 'display_name' => 'Update Permissions', 'module' => 'Permissions'],
            ['name' => 'permissions.delete', 'display_name' => 'Delete Permissions', 'module' => 'Permissions'],

            /* // Categories
            ['name' => 'categories.view',   'display_name' => 'View Categories',   'module' => 'Categories'],
            ['name' => 'categories.create', 'display_name' => 'Create Categories', 'module' => 'Categories'],
            ['name' => 'categories.update', 'display_name' => 'Update Categories', 'module' => 'Categories'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Categories', 'module' => 'Categories'],

            // Suppliers
            ['name' => 'suppliers.view',   'display_name' => 'View Suppliers',   'module' => 'Suppliers'],
            ['name' => 'suppliers.create', 'display_name' => 'Create Suppliers', 'module' => 'Suppliers'],
            ['name' => 'suppliers.update', 'display_name' => 'Update Suppliers', 'module' => 'Suppliers'],
            ['name' => 'suppliers.delete', 'display_name' => 'Delete Suppliers', 'module' => 'Suppliers'],

            // Items
            ['name' => 'items.view',   'display_name' => 'View Items',   'module' => 'Items'],
            ['name' => 'items.create', 'display_name' => 'Create Items', 'module' => 'Items'],
            ['name' => 'items.update', 'display_name' => 'Update Items', 'module' => 'Items'],
            ['name' => 'items.delete', 'display_name' => 'Delete Items', 'module' => 'Items'],

            // Inventory
            ['name' => 'inventory.view',    'display_name' => 'View Inventory',    'module' => 'Inventory'],
            ['name' => 'inventory.receive', 'display_name' => 'Receive Inventory', 'module' => 'Inventory'],
            ['name' => 'inventory.issue',   'display_name' => 'Issue Inventory',   'module' => 'Inventory'],
            ['name' => 'inventory.adjust',  'display_name' => 'Adjust Inventory',  'module' => 'Inventory'],

            // Reports
            ['name' => 'reports.view',   'display_name' => 'View Reports',   'module' => 'Reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'module' => 'Reports'], */
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}

<?php

use App\Models\Permission;
use Database\Seeders\PermissionSeeder;

it('seeds all permissions', function () {
    $this->seed(PermissionSeeder::class);

    expect(Permission::count())->toBeGreaterThan(0);

    $this->assertDatabaseHas('permissions', [
        'name' => 'users.view',
    ]);

    $this->assertDatabaseHas('permissions', [
        'name' => 'permissions.delete',
    ]);

});

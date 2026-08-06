<?php

namespace Tests\Traits;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait ActsAsAdmin
{
    protected function actingAsAdmin(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Administrator',
            'username' => 'admin',
            'employee_id' => 1,
            'is_active' => true,
        ], $attributes));

        Sanctum::actingAs($user);

        return $user;
    }
}
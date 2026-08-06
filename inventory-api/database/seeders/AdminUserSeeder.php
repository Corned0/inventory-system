<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => env('ADMIN_USERNAME')],
            [
                'name' => 'Inventory Administrator',
                'employee_id' => 1,
                'password' => Hash::make(env('ADMIN_PASSWORD')),
                'is_active' => true,
            ],
        );
    }
}
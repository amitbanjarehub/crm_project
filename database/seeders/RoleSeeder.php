<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'is_default' => false,
                'is_protected' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'is_default' => false,
                'is_protected' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'is_default' => false,
                'is_protected' => false,
            ],
            [
                'name' => 'Sales Executive',
                'slug' => 'sales-executive',
                'is_default' => false,
                'is_protected' => false,
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'is_default' => true,
                'is_protected' => false,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'is_default' => $role['is_default'],
                    'is_protected' => $role['is_protected'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
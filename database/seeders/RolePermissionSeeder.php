<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем разрешения
        $permissions = [
            'manage clients',
            'manage employees',
            'manage tokens',
            'view own profile',
            'extend subscription',
            'deactivate tokens',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Создаем роли
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Назначаем разрешения ролям
        $superAdminRole->givePermissionTo([
            'manage clients',
            'manage employees',
            'manage tokens',
            'extend subscription',
            'deactivate tokens'
        ]);

        $clientRole->givePermissionTo([
            'manage employees',
            'manage tokens',
            'view own profile'
        ]);

        $employeeRole->givePermissionTo([
            'view own profile'
        ]);

        // Создаем супер-админа
        $superAdmin = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
        ]);

        $superAdmin->assignRole('super-admin');
    }
}

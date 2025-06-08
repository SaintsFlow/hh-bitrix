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
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'client']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'employee']);
        }

        // Создаем роли для разных guards
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'client']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'employee']);

        // Назначаем разрешения ролям для каждого guard
        $superAdminRole->givePermissionTo([
            'manage clients',
            'manage employees',
            'manage tokens',
            'extend subscription',
            'deactivate tokens'
        ]);

        $clientRole->givePermissionTo(
            Permission::where('guard_name', 'client')->whereIn('name', [
                'manage employees',
                'manage tokens',
                'view own profile'
            ])->get()
        );

        $employeeRole->givePermissionTo(
            Permission::where('guard_name', 'employee')->whereIn('name', [
                'view own profile'
            ])->get()
        );

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

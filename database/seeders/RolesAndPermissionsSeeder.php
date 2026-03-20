<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Reservaciones
            'reservations.view',
            'reservations.create',
            'reservations.update',
            'reservations.cancel',
            'reservations.delete',
            // Mesas
            'tables.view',
            'tables.create',
            'tables.update',
            'tables.delete',
            // Zonas
            'zones.view',
            'zones.create',
            'zones.update',
            'zones.delete',
            // Usuarios
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            // Restaurante
            'restaurant.view',
            'restaurant.update',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $roles = [
            'super-admin' => $permissions,

            'admin' => [
                'reservations.view', 'reservations.create', 'reservations.update',
                'reservations.cancel', 'reservations.delete',
                'tables.view', 'tables.create', 'tables.update', 'tables.delete',
                'zones.view', 'zones.create', 'zones.update', 'zones.delete',
                'users.view', 'users.create', 'users.update',
                'restaurant.view', 'restaurant.update',
            ],

            'receptionist' => [
                'reservations.view', 'reservations.create',
                'reservations.update', 'reservations.cancel',
                'tables.view', 'zones.view',
            ],

            'staff' => [
                'reservations.view',
                'tables.view',
                'zones.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}

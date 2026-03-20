<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@rapidbistro.com'],
            [
                'name'     => 'Administrador',
                'phone'    => '+1-555-0001',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['super-admin']);

        $receptionist = User::firstOrCreate(
            ['email' => 'recepcion@rapidbistro.com'],
            [
                'name'     => 'Recepcionista',
                'phone'    => '+1-555-0002',
                'password' => Hash::make('password'),
            ]
        );
        $receptionist->syncRoles(['receptionist']);

        $staff = User::firstOrCreate(
            ['email' => 'staff@rapidbistro.com'],
            [
                'name'     => 'Personal',
                'phone'    => '+1-555-0003',
                'password' => Hash::make('password'),
            ]
        );
        $staff->syncRoles(['staff']);
    }
}

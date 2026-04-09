<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Super Administrador',
                'email' => 'superadmin@rapidbistro.com',
                'phone' => '+58 414 555 0001',
                'role'  => 'super-admin',
            ],
            [
                'name'  => 'Ana García (Admin)',
                'email' => 'admin@rapidbistro.com',
                'phone' => '+58 414 555 0002',
                'role'  => 'admin',
            ],
            [
                'name'  => 'Carlos López (Recepción)',
                'email' => 'recepcion@rapidbistro.com',
                'phone' => '+58 414 555 0003',
                'role'  => 'receptionist',
            ],
            [
                'name'  => 'María Torres (Maître)',
                'email' => 'maitre@rapidbistro.com',
                'phone' => '+58 414 555 0004',
                'role'  => 'maitre',
            ],
            [
                'name'  => 'Luis Pérez (Staff)',
                'email' => 'staff@rapidbistro.com',
                'phone' => '+58 414 555 0005',
                'role'  => 'staff',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'phone'    => $data['phone'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->syncRoles([$data['role']]);
        }

        $this->command->info('✓ Usuarios creados. Contraseña de todos: password');
    }
}

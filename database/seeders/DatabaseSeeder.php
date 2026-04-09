<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Permisos y roles
            RolesAndPermissionsSeeder::class,

            // 2. Restaurante: zonas y mesas
            RestaurantSeeder::class,

            // 3. Usuarios del sistema
            AdminUserSeeder::class,

            // 4. Turnos/servicios (horarios de atención)
            TimeSlotSeeder::class,

            // 5. Clientes CRM
            GuestSeeder::class,

            // 6. Reservaciones (pasadas, hoy, futuras)
            ReservationSeeder::class,

            // 7. Lista de espera (online + walk-ins)
            WaitlistSeeder::class,

            // 8. Fechas especiales y bloqueadas
            SpecialDateSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  RapidBistro — Datos de prueba listos  ');
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  superadmin@rapidbistro.com → super-admin');
        $this->command->info('  admin@rapidbistro.com      → admin');
        $this->command->info('  recepcion@rapidbistro.com  → receptionist');
        $this->command->info('  maitre@rapidbistro.com     → maitre');
        $this->command->info('  staff@rapidbistro.com      → staff');
        $this->command->info('  Contraseña de todos: password');
        $this->command->info('════════════════════════════════════════');
    }
}

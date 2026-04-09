<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'rapidbistro')->firstOrFail();

        // Tomar tablas de cada zona
        $mainTables    = Zone::where('name', 'Salón Principal')->first()?->tables ?? collect();
        $terraceTables = Zone::where('name', 'Terraza')->first()?->tables ?? collect();
        $kidsTables    = Zone::where('name', 'Área Kids')->first()?->tables ?? collect();

        $allTables = $mainTables->merge($terraceTables)->merge($kidsTables);

        if ($allTables->isEmpty()) {
            $this->command->warn('No hay mesas. Ejecutar RestaurantSeeder primero.');
            return;
        }

        $guests = [
            ['name' => 'Sofía Ramírez',    'email' => 'sofia.ramirez@email.com',  'phone' => '+58 414 601 2345'],
            ['name' => 'Miguel Fernández', 'email' => 'miguel.f@email.com',        'phone' => '+58 424 712 8800'],
            ['name' => 'Laura Castellanos','email' => 'lauracast@email.com',       'phone' => '+58 412 330 4456'],
            ['name' => 'Roberto Méndez',   'email' => 'rmendez@empresa.com',       'phone' => '+58 426 900 1122'],
            ['name' => 'Valentina Cruz',   'email' => 'vale.cruz@email.com',       'phone' => '+58 416 450 7890'],
            ['name' => 'Andrés Molina',    'email' => 'andres.m@email.com',        'phone' => '+58 414 333 9988'],
            ['name' => 'Carolina Herrera', 'email' => 'carolina.h@email.com',      'phone' => '+58 424 777 3344'],
            ['name' => 'José Martínez',    'email' => 'jose.martinez@email.com',   'phone' => '+58 412 200 5566'],
        ];

        $reservations = [];

        // ── PASADAS (últimos 7 días) ────────────────────────────────────────
        $pastReservations = [
            ['days' => -7, 'starts' => '12:00', 'ends' => '13:00', 'party' => 4, 'status' => 'completed', 'guest' => 0],
            ['days' => -6, 'starts' => '13:00', 'ends' => '14:00', 'party' => 2, 'status' => 'completed', 'guest' => 1],
            ['days' => -5, 'starts' => '19:00', 'ends' => '20:00', 'party' => 6, 'status' => 'completed', 'guest' => 2],
            ['days' => -4, 'starts' => '12:30', 'ends' => '13:30', 'party' => 2, 'status' => 'no_show',   'guest' => 3],
            ['days' => -3, 'starts' => '20:00', 'ends' => '21:00', 'party' => 4, 'status' => 'completed', 'guest' => 4],
            ['days' => -2, 'starts' => '13:00', 'ends' => '14:00', 'party' => 3, 'status' => 'completed', 'guest' => 5],
            ['days' => -2, 'starts' => '19:30', 'ends' => '20:30', 'party' => 2, 'status' => 'cancelled', 'guest' => 6],
            ['days' => -1, 'starts' => '12:00', 'ends' => '13:00', 'party' => 8, 'status' => 'completed', 'guest' => 7],
            ['days' => -1, 'starts' => '20:00', 'ends' => '21:00', 'party' => 2, 'status' => 'no_show',   'guest' => 0],
        ];

        // ── HOY ────────────────────────────────────────────────────────────
        $todayReservations = [
            ['starts' => '12:00', 'ends' => '13:00', 'party' => 2,  'status' => 'confirmed', 'guest' => 1],
            ['starts' => '12:30', 'ends' => '13:30', 'party' => 4,  'status' => 'confirmed', 'guest' => 2],
            ['starts' => '13:00', 'ends' => '14:00', 'party' => 6,  'status' => 'seated',    'guest' => 3],
            ['starts' => '13:30', 'ends' => '14:30', 'party' => 3,  'status' => 'confirmed', 'guest' => 4],
            ['starts' => '19:00', 'ends' => '20:00', 'party' => 2,  'status' => 'pending',   'guest' => 5],
            ['starts' => '19:30', 'ends' => '20:30', 'party' => 4,  'status' => 'confirmed', 'guest' => 6],
            ['starts' => '20:00', 'ends' => '21:00', 'party' => 8,  'status' => 'confirmed', 'guest' => 7],
            ['starts' => '20:30', 'ends' => '21:30', 'party' => 2,  'status' => 'pending',   'guest' => 0],
        ];

        // ── FUTURAS (próximos 14 días) ──────────────────────────────────────
        $futureReservations = [
            ['days' => 1,  'starts' => '12:00', 'ends' => '13:00', 'party' => 4,  'status' => 'confirmed', 'guest' => 0],
            ['days' => 1,  'starts' => '19:00', 'ends' => '20:00', 'party' => 2,  'status' => 'confirmed', 'guest' => 1],
            ['days' => 2,  'starts' => '13:00', 'ends' => '14:00', 'party' => 6,  'status' => 'pending',   'guest' => 2],
            ['days' => 2,  'starts' => '20:00', 'ends' => '21:00', 'party' => 10, 'status' => 'confirmed', 'guest' => 3],
            ['days' => 3,  'starts' => '12:30', 'ends' => '13:30', 'party' => 2,  'status' => 'confirmed', 'guest' => 4],
            ['days' => 5,  'starts' => '19:30', 'ends' => '20:30', 'party' => 4,  'status' => 'pending',   'guest' => 5],
            ['days' => 7,  'starts' => '12:00', 'ends' => '13:00', 'party' => 8,  'status' => 'confirmed', 'guest' => 6],
            ['days' => 7,  'starts' => '20:00', 'ends' => '21:00', 'party' => 2,  'status' => 'confirmed', 'guest' => 7],
            ['days' => 10, 'starts' => '13:00', 'ends' => '14:00', 'party' => 4,  'status' => 'pending',   'guest' => 0],
            ['days' => 14, 'starts' => '19:00', 'ends' => '20:00', 'party' => 6,  'status' => 'confirmed', 'guest' => 1],
        ];

        $tableIndex = 0;
        $getTable = function () use ($allTables, &$tableIndex) {
            $table = $allTables->values()[$tableIndex % $allTables->count()];
            $tableIndex++;
            return $table;
        };

        // Pasadas
        foreach ($pastReservations as $r) {
            $date = today()->subDays(abs($r['days']));
            $guest = $guests[$r['guest']];
            $table = $getTable();

            Reservation::firstOrCreate(
                [
                    'restaurant_id'    => $restaurant->id,
                    'table_id'         => $table->id,
                    'reservation_date' => $date->toDateString(),
                    'starts_at'        => $r['starts'],
                    'guest_email'      => $guest['email'],
                ],
                [
                    'ends_at'           => $r['ends'],
                    'party_size'        => $r['party'],
                    'status'            => $r['status'],
                    'guest_name'        => $guest['name'],
                    'guest_phone'       => $guest['phone'],
                    'confirmation_code' => strtoupper(Str::random(8)),
                    'cancelled_at'      => $r['status'] === 'cancelled' ? $date : null,
                ]
            );
        }

        // Hoy
        foreach ($todayReservations as $r) {
            $guest = $guests[$r['guest']];
            $table = $getTable();

            Reservation::firstOrCreate(
                [
                    'restaurant_id'    => $restaurant->id,
                    'table_id'         => $table->id,
                    'reservation_date' => today()->toDateString(),
                    'starts_at'        => $r['starts'],
                    'guest_email'      => $guest['email'],
                ],
                [
                    'ends_at'           => $r['ends'],
                    'party_size'        => $r['party'],
                    'status'            => $r['status'],
                    'guest_name'        => $guest['name'],
                    'guest_phone'       => $guest['phone'],
                    'confirmation_code' => strtoupper(Str::random(8)),
                ]
            );
        }

        // Futuras
        foreach ($futureReservations as $r) {
            $date = today()->addDays($r['days']);
            $guest = $guests[$r['guest']];
            $table = $getTable();

            Reservation::firstOrCreate(
                [
                    'restaurant_id'    => $restaurant->id,
                    'table_id'         => $table->id,
                    'reservation_date' => $date->toDateString(),
                    'starts_at'        => $r['starts'],
                    'guest_email'      => $guest['email'],
                ],
                [
                    'ends_at'           => $r['ends'],
                    'party_size'        => $r['party'],
                    'status'            => $r['status'],
                    'guest_name'        => $guest['name'],
                    'guest_phone'       => $guest['phone'],
                    'confirmation_code' => strtoupper(Str::random(8)),
                ]
            );
        }

        $total = count($pastReservations) + count($todayReservations) + count($futureReservations);
        $this->command->info("✓ {$total} reservaciones creadas (pasadas, hoy y futuras)");
    }
}

<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\SpecialDate;
use Illuminate\Database\Seeder;

class SpecialDateSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'rapidbistro')->firstOrFail();

        $dates = [
            [
                'name'            => 'Día de las Madres',
                'date'            => today()->addDays(15)->toDateString(),
                'type'            => 'event',
                'color'           => '#f59e0b',
                'booking_allowed' => true,
                'description'           => 'Alta demanda esperada. Menú especial del día.',
            ],
            [
                'name'            => 'Mantenimiento de cocina',
                'date'            => today()->addDays(22)->toDateString(),
                'type'            => 'blocked',
                'color'           => '#ef4444',
                'booking_allowed' => false,
                'description'           => 'Limpieza profunda y mantenimiento de equipos.',
            ],
            [
                'name'            => 'Evento corporativo',
                'date'            => today()->addDays(7)->toDateString(),
                'type'            => 'limited',
                'color'           => '#8b5cf6',
                'booking_allowed' => false,
                'description'           => 'Reservado para evento privado empresa XYZ. Solo terraza disponible para clientes.',
            ],
            [
                'name'            => 'Año Nuevo',
                'date'            => today()->addMonths(3)->startOfMonth()->addMonths(3)->format('Y-12-31'),
                'type'            => 'event',
                'color'           => '#f59e0b',
                'booking_allowed' => true,
                'description'           => 'Cena de gala. Reservación obligatoria. Menú cerrado.',
            ],
        ];

        foreach ($dates as $data) {
            SpecialDate::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'date' => $data['date']],
                array_merge($data, ['restaurant_id' => $restaurant->id])
            );
        }

        $this->command->info('✓ Fechas especiales creadas');
    }
}

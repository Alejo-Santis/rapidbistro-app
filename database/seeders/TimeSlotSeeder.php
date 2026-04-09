<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'rapidbistro')->firstOrFail();

        $services = [
            [
                'name'                        => 'Almuerzo',
                'opens_at'                    => '11:00',
                'closes_at'                   => '16:00',
                'slot_duration_minutes'       => 60,
                'max_concurrent_reservations' => 8,
            ],
            [
                'name'                        => 'Merienda',
                'opens_at'                    => '16:00',
                'closes_at'                   => '18:30',
                'slot_duration_minutes'       => 60,
                'max_concurrent_reservations' => 5,
            ],
            [
                'name'                        => 'Cena',
                'opens_at'                    => '18:30',
                'closes_at'                   => '22:00',
                'slot_duration_minutes'       => 60,
                'max_concurrent_reservations' => 8,
            ],
        ];

        // Entre semana: Almuerzo + Cena (sin merienda)
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        // Fin de semana: los 3 servicios
        $weekend = ['saturday', 'sunday'];

        foreach ($weekdays as $day) {
            foreach (['Almuerzo', 'Cena'] as $serviceName) {
                $svc = collect($services)->firstWhere('name', $serviceName);
                TimeSlot::firstOrCreate(
                    ['restaurant_id' => $restaurant->id, 'day_of_week' => $day, 'name' => $svc['name']],
                    array_merge($svc, ['restaurant_id' => $restaurant->id, 'day_of_week' => $day, 'is_active' => true])
                );
            }
        }

        foreach ($weekend as $day) {
            foreach ($services as $svc) {
                TimeSlot::firstOrCreate(
                    ['restaurant_id' => $restaurant->id, 'day_of_week' => $day, 'name' => $svc['name']],
                    array_merge($svc, ['restaurant_id' => $restaurant->id, 'day_of_week' => $day, 'is_active' => true])
                );
            }
        }

        $this->command->info('✓ Turnos/servicios creados (Almuerzo, Merienda, Cena)');
    }
}

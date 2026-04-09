<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\Waitlist;
use Illuminate\Database\Seeder;

class WaitlistSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'rapidbistro')->firstOrFail();

        // Lista de espera online (portal público)
        $online = [
            [
                'guest_name'     => 'Patricia Suárez',
                'guest_email'    => 'patricia.s@email.com',
                'guest_phone'    => '+58 414 900 1234',
                'preferred_date' => today()->addDays(2)->toDateString(),
                'preferred_time' => '19:00',
                'party_size'     => 4,
                'status'         => 'pending',
                'source'         => 'online',
                'notes'          => 'Celebración de cumpleaños',
            ],
            [
                'guest_name'     => 'Diego Rivas',
                'guest_email'    => 'diego.rivas@email.com',
                'guest_phone'    => '+58 424 800 5678',
                'preferred_date' => today()->addDays(3)->toDateString(),
                'preferred_time' => '12:30',
                'party_size'     => 2,
                'status'         => 'pending',
                'source'         => 'online',
                'notes'          => null,
            ],
            [
                'guest_name'     => 'Marcela Ortiz',
                'guest_email'    => 'marcela.o@email.com',
                'guest_phone'    => '+58 416 700 9900',
                'preferred_date' => today()->addDays(1)->toDateString(),
                'preferred_time' => '20:00',
                'party_size'     => 6,
                'status'         => 'notified',
                'source'         => 'online',
                'notes'          => 'Mesa grande si es posible',
                'notified_at'    => now()->subHours(1),
            ],
        ];

        // Walk-ins de hoy
        $walkIns = [
            [
                'guest_name'     => 'Familia Rodríguez',
                'guest_email'    => null,
                'guest_phone'    => '+58 412 444 7788',
                'preferred_date' => today()->toDateString(),
                'party_size'     => 5,
                'status'         => 'pending',
                'source'         => 'walk_in',
                'arrived_at'     => now()->subMinutes(25),
                'notes'          => 'Niño pequeño',
            ],
            [
                'guest_name'     => 'Juan Torres',
                'guest_email'    => null,
                'guest_phone'    => '+58 414 222 3344',
                'preferred_date' => today()->toDateString(),
                'party_size'     => 2,
                'status'         => 'pending',
                'source'         => 'walk_in',
                'arrived_at'     => now()->subMinutes(10),
                'notes'          => null,
            ],
        ];

        foreach (array_merge($online, $walkIns) as $data) {
            Waitlist::firstOrCreate(
                [
                    'restaurant_id'  => $restaurant->id,
                    'guest_name'     => $data['guest_name'],
                    'preferred_date' => $data['preferred_date'],
                    'source'         => $data['source'],
                ],
                array_merge($data, ['restaurant_id' => $restaurant->id])
            );
        }

        $this->command->info('✓ Lista de espera creada (online + walk-ins)');
    }
}

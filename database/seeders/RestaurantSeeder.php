<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'rapidbistro'],
            [
                'name'      => 'RapidBistro Fast Food',
                'address'   => 'Av. Libertador 1450, Local 3, Caracas',
                'phone'     => '+58 212 555 0100',
                'email'     => 'hola@rapidbistro.com',
                'is_active' => true,
                'settings'  => [
                    'currency'             => 'USD',
                    'timezone'             => 'America/Caracas',
                    'cancellation_policy'  => 'Cancelación gratuita hasta 1 hora antes de la reservación. Pasado ese tiempo, la mesa será liberada.',
                    'default_slot_minutes' => 60,
                    'max_party_size'       => 12,
                ],
            ]
        );

        $zonesData = [
            [
                'name'        => 'Salón Principal',
                'description' => 'Área interior climatizada, ambiente familiar.',
                'location'    => 'indoor',
                'sort_order'  => 1,
                'tables'      => [
                    ['number' => '1',  'capacity' => 2, 'min_capacity' => 1],
                    ['number' => '2',  'capacity' => 2, 'min_capacity' => 1],
                    ['number' => '3',  'capacity' => 4, 'min_capacity' => 2],
                    ['number' => '4',  'capacity' => 4, 'min_capacity' => 2],
                    ['number' => '5',  'capacity' => 4, 'min_capacity' => 2],
                    ['number' => '6',  'capacity' => 6, 'min_capacity' => 3],
                    ['number' => '7',  'capacity' => 6, 'min_capacity' => 3],
                    ['number' => '8',  'capacity' => 8, 'min_capacity' => 4],
                ],
            ],
            [
                'name'        => 'Terraza',
                'description' => 'Área al aire libre, perfecta para grupos.',
                'location'    => 'outdoor',
                'sort_order'  => 2,
                'tables'      => [
                    ['number' => 'T1', 'capacity' => 4, 'min_capacity' => 2],
                    ['number' => 'T2', 'capacity' => 4, 'min_capacity' => 2],
                    ['number' => 'T3', 'capacity' => 6, 'min_capacity' => 3],
                    ['number' => 'T4', 'capacity' => 8, 'min_capacity' => 4],
                    ['number' => 'T5', 'capacity' => 10,'min_capacity' => 5],
                ],
            ],
            [
                'name'        => 'Área Kids',
                'description' => 'Zona familiar con área de juegos para niños.',
                'location'    => 'indoor',
                'sort_order'  => 3,
                'tables'      => [
                    ['number' => 'K1', 'capacity' => 4, 'min_capacity' => 2],
                    ['number' => 'K2', 'capacity' => 6, 'min_capacity' => 3],
                    ['number' => 'K3', 'capacity' => 6, 'min_capacity' => 3],
                ],
            ],
        ];

        foreach ($zonesData as $zoneData) {
            $tablesData = $zoneData['tables'];
            unset($zoneData['tables']);

            $zone = Zone::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $zoneData['name']],
                array_merge($zoneData, ['is_active' => true, 'restaurant_id' => $restaurant->id])
            );

            foreach ($tablesData as $tableRow) {
                Table::firstOrCreate(
                    ['zone_id' => $zone->id, 'number' => $tableRow['number']],
                    [
                        'capacity'     => $tableRow['capacity'],
                        'min_capacity' => $tableRow['min_capacity'],
                        'status'       => 'available',
                    ]
                );
            }
        }
    }
}

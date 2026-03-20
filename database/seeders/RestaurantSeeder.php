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
                'name'      => 'RapidBistro',
                'address'   => 'Av. Principal 123, Ciudad',
                'phone'     => '+1-555-0100',
                'email'     => 'info@rapidbistro.com',
                'is_active' => true,
                'settings'  => [
                    'currency'             => 'USD',
                    'cancellation_policy'  => 'Cancelación gratuita hasta 2 horas antes de la reservación.',
                    'default_slot_minutes' => 90,
                ],
            ]
        );

        $zonesData = [
            [
                'name'        => 'Salón Principal',
                'description' => 'Área principal del restaurante, ambiente climatizado.',
                'location'    => 'indoor',
                'sort_order'  => 1,
                'tables'      => [
                    ['number' => '1', 'capacity' => 2],
                    ['number' => '2', 'capacity' => 4],
                    ['number' => '3', 'capacity' => 4],
                    ['number' => '4', 'capacity' => 6],
                    ['number' => '5', 'capacity' => 6],
                    ['number' => '6', 'capacity' => 8],
                ],
            ],
            [
                'name'        => 'Terraza',
                'description' => 'Área al aire libre con vista al jardín.',
                'location'    => 'outdoor',
                'sort_order'  => 2,
                'tables'      => [
                    ['number' => '7',  'capacity' => 2],
                    ['number' => '8',  'capacity' => 4],
                    ['number' => '9',  'capacity' => 4],
                    ['number' => '10', 'capacity' => 6],
                ],
            ],
            [
                'name'        => 'Bar',
                'description' => 'Área de barra y cócteles.',
                'location'    => 'bar',
                'sort_order'  => 3,
                'tables'      => [
                    ['number' => 'B1', 'capacity' => 2],
                    ['number' => 'B2', 'capacity' => 2],
                    ['number' => 'B3', 'capacity' => 4],
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
                        'min_capacity' => 1,
                        'status'       => 'available',
                    ]
                );
            }
        }
    }
}

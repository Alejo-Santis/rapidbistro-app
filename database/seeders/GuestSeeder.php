<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'rapidbistro')->firstOrFail();

        $guests = [
            [
                'name'         => 'Sofía Ramírez',
                'email'        => 'sofia.ramirez@email.com',
                'phone'        => '+58 414 601 2345',
                'birthday'     => '1990-03-15',
                'allergies'    => 'Mariscos, Nueces',
                'preferences'  => 'Mesa cerca de la ventana. Sin picante.',
                'staff_notes'  => 'Cliente frecuente, visita los viernes.',
                'is_vip'       => true,
            ],
            [
                'name'         => 'Miguel Fernández',
                'email'        => 'miguel.f@email.com',
                'phone'        => '+58 424 712 8800',
                'birthday'     => '1985-07-22',
                'anniversary'  => '2012-11-10',
                'preferences'  => 'Prefiere zona tranquila.',
                'staff_notes'  => null,
                'is_vip'       => false,
            ],
            [
                'name'         => 'Laura Castellanos',
                'email'        => 'lauracast@email.com',
                'phone'        => '+58 412 330 4456',
                'birthday'     => '1995-12-01',
                'allergies'    => 'Gluten',
                'preferences'  => 'Siempre pide menú infantil para sus hijos.',
                'staff_notes'  => 'Viene con 2 niños pequeños. Solicitar sillas altas.',
                'is_vip'       => false,
            ],
            [
                'name'         => 'Roberto Méndez',
                'email'        => 'rmendez@empresa.com',
                'phone'        => '+58 426 900 1122',
                'birthday'     => null,
                'preferences'  => 'Almuerzos de negocios, mesa privada.',
                'staff_notes'  => 'Trae grupos de 6-8 personas.',
                'is_vip'       => true,
            ],
            [
                'name'         => 'Valentina Cruz',
                'email'        => 'vale.cruz@email.com',
                'phone'        => '+58 416 450 7890',
                'birthday'     => '1998-05-30',
                'allergies'    => null,
                'preferences'  => 'Vegetariana estricta.',
                'staff_notes'  => null,
                'is_vip'       => false,
            ],
        ];

        foreach ($guests as $data) {
            Guest::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'email' => $data['email']],
                array_merge($data, ['restaurant_id' => $restaurant->id])
            );
        }

        $this->command->info('✓ Clientes CRM creados');
    }
}

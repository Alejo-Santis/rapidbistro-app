<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'zone_id'      => Zone::factory(),
            'number'       => fake()->unique()->numberBetween(1, 100),
            'capacity'     => fake()->randomElement([2, 4, 6, 8]),
            'min_capacity' => 1,
            'status'       => 'available',
        ];
    }
}

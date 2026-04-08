<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name'          => fake()->randomElement(['Salón principal', 'Terraza', 'Bar', 'Privado', 'Jardín']),
            'description'   => fake()->sentence(),
            'is_active'     => true,
            'sort_order'    => fake()->numberBetween(1, 10),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id'  => Restaurant::factory(),
            'guest_name'     => fake()->name(),
            'guest_email'    => fake()->email(),
            'guest_phone'    => fake()->phoneNumber(),
            'party_size'     => fake()->numberBetween(1, 8),
            'preferred_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'preferred_time' => sprintf('%02d:00', fake()->numberBetween(12, 21)),
            'status'         => 'pending',
            'notes'          => null,
            'source'         => 'online',
        ];
    }

    public function walkIn(): static
    {
        return $this->state([
            'source'         => 'walk_in',
            'status'         => 'pending',
            'arrived_at'     => now(),
            'preferred_date' => today(),
        ]);
    }
}

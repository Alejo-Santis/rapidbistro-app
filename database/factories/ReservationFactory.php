<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $hour     = fake()->numberBetween(12, 20);
        $startsAt = sprintf('%02d:00', $hour);
        $endsAt   = sprintf('%02d:00', $hour + 2);

        return [
            'restaurant_id'     => Restaurant::factory(),
            'table_id'          => Table::factory(),
            'reservation_date'  => fake()->dateTimeBetween('-7 days', '+30 days')->format('Y-m-d'),
            'starts_at'         => $startsAt,
            'ends_at'           => $endsAt,
            'party_size'        => fake()->numberBetween(1, 8),
            'status'            => 'confirmed',
            'guest_name'        => fake()->name(),
            'guest_email'       => fake()->email(),
            'guest_phone'       => fake()->phoneNumber(),
            'confirmation_code' => strtoupper(Str::random(8)),
            'notes'             => null,
            'internal_notes'    => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function seated(): static
    {
        return $this->state(['status' => 'seated']);
    }

    public function noShow(): static
    {
        return $this->state(['status' => 'no_show']);
    }

    public function today(): static
    {
        return $this->state(['reservation_date' => today()->toDateString()]);
    }
}

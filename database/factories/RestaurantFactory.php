<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RestaurantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name'      => $name,
            'slug'      => Str::slug($name),
            'address'   => fake()->address(),
            'phone'     => fake()->phoneNumber(),
            'email'     => fake()->companyEmail(),
            'is_active' => true,
        ];
    }
}

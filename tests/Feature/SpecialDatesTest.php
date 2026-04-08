<?php

use App\Models\Restaurant;
use App\Models\SpecialDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::factory()->create();
    $this->user       = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

test('special dates page is accessible', function () {
    $this->get('/special-dates')->assertStatus(200);
});

test('can create a special date', function () {
    $this->post('/special-dates', [
        'date'            => now()->addDays(10)->toDateString(),
        'name'            => 'Cena de San Valentín',
        'type'            => 'event',
        'color'           => '#f59e0b',
        'booking_allowed' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('special_dates', [
        'name' => 'Cena de San Valentín',
        'type' => 'event',
    ]);
});

test('special date requires valid color format', function () {
    $this->post('/special-dates', [
        'date'            => now()->addDays(10)->toDateString(),
        'name'            => 'Test',
        'type'            => 'event',
        'color'           => 'not-a-color',
        'booking_allowed' => true,
    ])->assertSessionHasErrors('color');
});

test('can update a special date', function () {
    $sd = SpecialDate::create([
        'restaurant_id'  => $this->restaurant->id,
        'date'           => now()->addDays(10)->toDateString(),
        'name'           => 'Original',
        'type'           => 'event',
        'color'          => '#f59e0b',
        'booking_allowed'=> true,
    ]);

    $this->put("/special-dates/{$sd->id}", [
        'date'            => $sd->date->toDateString(),
        'name'            => 'Actualizado',
        'type'            => 'blocked',
        'color'           => '#ef4444',
        'booking_allowed' => false,
    ])->assertRedirect();

    expect($sd->fresh()->name)->toBe('Actualizado');
    expect($sd->fresh()->type)->toBe('blocked');
});

test('can delete a special date', function () {
    $sd = SpecialDate::create([
        'restaurant_id'  => $this->restaurant->id,
        'date'           => now()->addDays(10)->toDateString(),
        'name'           => 'Para borrar',
        'type'           => 'event',
        'color'          => '#f59e0b',
        'booking_allowed'=> true,
    ]);

    $this->delete("/special-dates/{$sd->id}")->assertRedirect();

    $this->assertDatabaseMissing('special_dates', ['id' => $sd->id]);
});

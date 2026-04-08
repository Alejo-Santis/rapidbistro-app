<?php

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::factory()->create();
    $this->zone       = Zone::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->table      = Table::factory()->create(['zone_id' => $this->zone->id]);
    $this->user       = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

test('reservations index is accessible', function () {
    $this->get('/reservations')->assertStatus(200);
});

test('can create a reservation', function () {
    $payload = [
        'table_id'         => $this->table->id,
        'reservation_date' => today()->addDay()->toDateString(),
        'starts_at'        => '19:00',
        'ends_at'          => '21:00',
        'party_size'       => 4,
        'guest_name'       => 'Juan Pérez',
        'guest_email'      => 'juan@example.com',
        'guest_phone'      => '+57 300 1234567',
        'status'           => 'confirmed',
    ];

    $this->post('/reservations', $payload)->assertRedirect();

    $this->assertDatabaseHas('reservations', [
        'guest_name'  => 'Juan Pérez',
        'guest_email' => 'juan@example.com',
        'party_size'  => 4,
        'status'      => 'pending',
    ]);
});

test('reservation requires guest name', function () {
    $this->post('/reservations', [
        'table_id'         => $this->table->id,
        'reservation_date' => today()->addDay()->toDateString(),
        'starts_at'        => '19:00',
        'ends_at'          => '21:00',
        'party_size'       => 2,
        'status'           => 'confirmed',
        // guest_name missing
    ])->assertSessionHasErrors('guest_name');
});

test('can update reservation status via maitre endpoint', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'confirmed',
    ]);

    $this->patch("/reservations/{$reservation->uuid}/status", ['status' => 'seated'])
        ->assertRedirect();

    expect($reservation->fresh()->status)->toBe('seated');
});

test('mark-no-show updates status and creates log', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'confirmed',
    ]);

    $this->patch("/reservations/{$reservation->uuid}/mark-no-show")
        ->assertRedirect();

    expect($reservation->fresh()->status)->toBe('no_show');

    $this->assertDatabaseHas('reservation_status_logs', [
        'reservation_id'  => $reservation->id,
        'previous_status' => 'confirmed',
        'new_status'      => 'no_show',
    ]);
});

test('can delete a reservation', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
    ]);

    $this->delete("/reservations/{$reservation->uuid}")->assertRedirect();

    $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
});

test('confirmation code is auto-generated on create', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
    ]);

    expect($reservation->confirmation_code)->not->toBeEmpty();
    expect(strlen($reservation->confirmation_code))->toBe(8);
});

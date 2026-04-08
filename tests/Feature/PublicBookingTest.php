<?php

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\TimeSlot;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->restaurant = Restaurant::factory()->create();
    $this->zone       = Zone::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->table      = Table::factory()->create(['zone_id' => $this->zone->id, 'capacity' => 4]);

    TimeSlot::create([
        'restaurant_id'              => $this->restaurant->id,
        'day_of_week'                => strtolower(now()->addDay()->englishDayOfWeek),
        'name'                       => 'Cena',
        'opens_at'                   => '19:00',
        'closes_at'                  => '23:00',
        'slot_duration_minutes'      => 120,
        'max_concurrent_reservations'=> 10,
        'is_active'                  => true,
    ]);
});

test('public booking page is accessible without auth', function () {
    $this->get('/reservar')->assertStatus(200);
});

test('availability endpoint returns time slots', function () {
    $date = now()->addDay()->toDateString();

    $this->getJson("/reservar/disponibilidad?date={$date}&party_size=2")
        ->assertStatus(200)
        ->assertJsonStructure(['available']);
});

test('guest can create a reservation via public portal', function () {
    Mail::fake();

    $date = now()->addDay()->toDateString();

    $this->post('/reservar', [
        'reservation_date' => $date,
        'starts_at'        => '19:00',
        'party_size'       => 2,
        'guest_name'       => 'María García',
        'guest_email'      => 'maria@example.com',
        'guest_phone'      => '+57 310 1234567',
    ])->assertRedirect();

    $this->assertDatabaseHas('reservations', [
        'guest_name'  => 'María García',
        'guest_email' => 'maria@example.com',
        'status'      => 'confirmed',
    ]);
});

test('confirmation page shows reservation details', function () {
    $reservation = \App\Models\Reservation::factory()->create([
        'restaurant_id'    => $this->restaurant->id,
        'table_id'         => $this->table->id,
        'confirmation_code'=> 'TESTCODE',
    ]);

    $this->get('/reservar/confirmacion/TESTCODE')->assertStatus(200);
});

test('waitlist form is accessible', function () {
    $this->get('/reservar/lista-espera')->assertStatus(200);
});

test('guest can join waitlist', function () {
    $this->post('/reservar/lista-espera', [
        'guest_name'     => 'Carlos López',
        'guest_email'    => 'carlos@example.com',
        'guest_phone'    => '+57 320 9876543',
        'party_size'     => 3,
        'preferred_date' => now()->addDays(3)->toDateString(),
        'preferred_time' => '20:00',
    ])->assertRedirect();

    $this->assertDatabaseHas('waitlists', [
        'guest_name'  => 'Carlos López',
        'guest_email' => 'carlos@example.com',
    ]);
});

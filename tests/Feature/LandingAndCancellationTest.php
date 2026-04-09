<?php

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->restaurant = Restaurant::factory()->create(['is_active' => true]);
    $this->zone       = Zone::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->table      = Table::factory()->create(['zone_id' => $this->zone->id]);
});

// ── Landing ──────────────────────────────────────────────────────────────────

test('landing page is accessible without auth', function () {
    $this->get('/')->assertStatus(200);
});

test('landing page redirects authenticated users to dashboard', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $this->get('/')->assertRedirect(route('dashboard'));
});

test('landing page contains booking and waitlist links', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Landing/Index')
        ->has('restaurant')
        ->has('schedule')
    );
});

// ── Cancelación ───────────────────────────────────────────────────────────────

test('reservation has a cancellation token after creation', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
    ]);

    expect($reservation->cancellation_token)->not->toBeNull();
    expect(strlen($reservation->cancellation_token))->toBe(48);
});

test('cancel form page is accessible with valid token', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'confirmed',
    ]);

    $this->get("/reservar/cancelar/{$reservation->cancellation_token}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Booking/Cancel')
            ->where('canCancel', true)
        );
});

test('cancel form returns 404 with invalid token', function () {
    $this->get('/reservar/cancelar/token-invalido-que-no-existe')
        ->assertStatus(404);
});

test('guest can cancel their reservation via token', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'confirmed',
    ]);

    $token = $reservation->cancellation_token;

    $this->post("/reservar/cancelar/{$token}")
        ->assertRedirect(route('booking.cancel.done', $reservation->confirmation_code));

    expect($reservation->fresh()->status)->toBe('cancelled');
    expect($reservation->fresh()->cancelled_at)->not->toBeNull();
    expect($reservation->fresh()->cancellation_token)->toBeNull();
});

test('cancellation token is invalidated after use', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'confirmed',
    ]);

    $token = $reservation->cancellation_token;

    $this->post("/reservar/cancelar/{$token}");

    // El mismo token ya no funciona
    $this->post("/reservar/cancelar/{$token}")->assertStatus(404);
});

test('seated reservation cannot be cancelled via token', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'seated',
    ]);

    $this->get("/reservar/cancelar/{$reservation->cancellation_token}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->where('canCancel', false));
});

test('cancel done page is accessible', function () {
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id'      => $this->table->id,
        'status'        => 'cancelled',
    ]);

    $this->get("/reservar/cancelado/{$reservation->confirmation_code}")
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Booking/CancelDone'));
});

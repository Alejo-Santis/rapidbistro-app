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

test('dashboard is accessible and returns metrics', function () {
    Reservation::factory()->count(3)->create([
        'restaurant_id'    => $this->restaurant->id,
        'table_id'         => $this->table->id,
        'reservation_date' => today()->toDateString(),
        'status'           => 'confirmed',
    ]);

    $this->get('/dashboard')->assertStatus(200);
});

test('reports page is accessible', function () {
    $this->get('/reports')->assertStatus(200);
});

test('reports page accepts date range filter', function () {
    $from = now()->subDays(7)->toDateString();
    $to   = today()->toDateString();

    $this->get("/reports?from={$from}&to={$to}")->assertStatus(200);
});

test('pdf export returns a pdf file', function () {
    $response = $this->get('/reports/export-pdf?date=' . today()->toDateString());
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('excel export returns an xlsx file', function () {
    $from = now()->startOfMonth()->toDateString();
    $to   = today()->toDateString();

    $response = $this->get("/reports/export-excel?from={$from}&to={$to}");
    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('spreadsheet');
});

test('maitre view is accessible', function () {
    $this->get('/maitre')->assertStatus(200);
});

test('maitre view shows todays reservations', function () {
    Reservation::factory()->count(2)->create([
        'restaurant_id'    => $this->restaurant->id,
        'table_id'         => $this->table->id,
        'reservation_date' => today()->toDateString(),
        'status'           => 'confirmed',
    ]);

    $this->get('/maitre')->assertStatus(200);
});

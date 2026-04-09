<?php

use App\Console\Commands\SendReservationReminders;
use App\Mail\ReservationReminderMail;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;

// DatabaseMigrations runs migrate:fresh (committed data) so artisan can see it across connections
uses(DatabaseMigrations::class);

test('reminder command runs without errors', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->artisan('reservations:send-reminders')
        ->assertExitCode(0);
});

test('reminder command sends 24h email for reservations tomorrow at current hour', function () {
    $this->freezeTime();
    Mail::fake();

    $restaurant = Restaurant::factory()->create();
    $zone       = Zone::factory()->create(['restaurant_id' => $restaurant->id]);
    $table      = Table::factory()->create(['zone_id' => $zone->id]);

    $targetTime = now()->addDay()->format('H:i');

    Reservation::factory()->create([
        'restaurant_id'    => $restaurant->id,
        'table_id'         => $table->id,
        'reservation_date' => now()->addDay()->toDateString(),
        'starts_at'        => $targetTime,
        'status'           => 'confirmed',
        'guest_email'      => 'test24h@example.com',
    ]);

    $this->artisan('reservations:send-reminders')->assertExitCode(0);

    Mail::assertQueued(ReservationReminderMail::class, fn ($mail) =>
        $mail->hasTo('test24h@example.com')
    );
});

test('reminder command sends 2h email for reservations today in 2 hours', function () {
    $this->freezeTime();
    Mail::fake();

    $restaurant = Restaurant::factory()->create();
    $zone       = Zone::factory()->create(['restaurant_id' => $restaurant->id]);
    $table      = Table::factory()->create(['zone_id' => $zone->id]);

    $targetTime = now()->addHours(2)->format('H:i');

    Reservation::factory()->create([
        'restaurant_id'    => $restaurant->id,
        'table_id'         => $table->id,
        'reservation_date' => today()->toDateString(),
        'starts_at'        => $targetTime,
        'status'           => 'confirmed',
        'guest_email'      => 'test2h@example.com',
    ]);

    $this->artisan('reservations:send-reminders')->assertExitCode(0);

    Mail::assertQueued(ReservationReminderMail::class, fn ($mail) =>
        $mail->hasTo('test2h@example.com')
    );
});

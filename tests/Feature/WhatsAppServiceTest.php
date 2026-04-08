<?php

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('whatsapp service reports unconfigured when env vars are missing', function () {
    config(['services.whatsapp.token' => null]);
    config(['services.whatsapp.phone_number_id' => null]);

    $service = new WhatsAppService();
    expect($service->isConfigured())->toBeFalse();
});

test('whatsapp service skips send when not configured', function () {
    config(['services.whatsapp.token' => null]);

    $restaurant = Restaurant::factory()->create();
    $zone       = Zone::factory()->create(['restaurant_id' => $restaurant->id]);
    $table      = Table::factory()->create(['zone_id' => $zone->id]);
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $restaurant->id,
        'table_id'      => $table->id,
        'guest_phone'   => '+573001234567',
    ]);

    $service = new WhatsAppService();
    $result  = $service->sendConfirmation($reservation);

    expect($result)->toBeFalse();
});

test('whatsapp service skips send when reservation has no phone', function () {
    config(['services.whatsapp.token' => 'fake-token']);
    config(['services.whatsapp.phone_number_id' => '12345']);

    $restaurant  = Restaurant::factory()->create();
    $zone        = Zone::factory()->create(['restaurant_id' => $restaurant->id]);
    $table       = Table::factory()->create(['zone_id' => $zone->id]);
    $reservation = Reservation::factory()->create([
        'restaurant_id' => $restaurant->id,
        'table_id'      => $table->id,
        'guest_phone'   => null,
    ]);

    $service = new WhatsAppService();
    $result  = $service->sendConfirmation($reservation);

    expect($result)->toBeFalse();
});

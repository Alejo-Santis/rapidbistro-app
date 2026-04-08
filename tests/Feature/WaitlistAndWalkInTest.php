<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::factory()->create();
    $this->user       = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

test('internal waitlist index is accessible', function () {
    $this->get('/waitlist')->assertStatus(200);
});

test('walk-in index is accessible', function () {
    $this->get('/walk-in')->assertStatus(200);
});

test('can add a walk-in group', function () {
    $this->post('/walk-in', [
        'guest_name'  => 'Familia Torres',
        'party_size'  => 4,
        'guest_phone' => '+57 300 5556666',
        'notes'       => 'Silla alta para bebé',
    ])->assertRedirect();

    $this->assertDatabaseHas('waitlists', [
        'guest_name' => 'Familia Torres',
        'source'     => 'walk_in',
        'status'     => 'pending',
    ]);
});

test('can seat a walk-in group', function () {
    $entry = Waitlist::factory()->walkIn()->create([
        'restaurant_id' => $this->restaurant->id,
    ]);

    $this->patch("/walk-in/{$entry->uuid}/seat")->assertRedirect();

    expect($entry->fresh()->status)->toBe('booked');
});

test('can remove a walk-in group', function () {
    $entry = Waitlist::factory()->walkIn()->create([
        'restaurant_id' => $this->restaurant->id,
    ]);

    $this->patch("/walk-in/{$entry->uuid}/remove")->assertRedirect();

    expect($entry->fresh()->status)->toBe('expired');
});

test('can notify a waitlist entry', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $entry = Waitlist::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'status'        => 'pending',
    ]);

    $this->patch("/waitlist/{$entry->uuid}/notify")->assertRedirect();

    expect($entry->fresh()->status)->toBe('notified');
    expect($entry->fresh()->notified_at)->not->toBeNull();
});

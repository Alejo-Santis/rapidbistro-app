<?php

use App\Models\Guest;
use App\Models\Restaurant;
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

test('guests index is accessible', function () {
    $this->get('/guests')->assertStatus(200);
});

test('can create a guest', function () {
    $this->post('/guests', [
        'name'        => 'Ana Martínez',
        'email'       => 'ana@example.com',
        'phone'       => '+57 311 1234567',
        'is_vip'      => false,
        'allergies'   => 'Gluten',
        'preferences' => 'Mesa ventana',
    ])->assertRedirect();

    $this->assertDatabaseHas('guests', [
        'email'     => 'ana@example.com',
        'allergies' => 'Gluten',
    ]);
});

test('guest email is unique per restaurant', function () {
    Guest::create([
        'restaurant_id' => $this->restaurant->id,
        'name'          => 'Existente',
        'email'         => 'duplicate@example.com',
    ]);

    $this->post('/guests', [
        'name'  => 'Otro',
        'email' => 'duplicate@example.com',
    ])->assertSessionHasErrors('email');
});

test('can update a guest', function () {
    $guest = Guest::create([
        'restaurant_id' => $this->restaurant->id,
        'name'          => 'Original',
        'email'         => 'guest@example.com',
    ]);

    $this->put("/guests/{$guest->uuid}", [
        'name'     => 'Actualizado',
        'email'    => 'guest@example.com',
        'is_vip'   => true,
        'allergies'=> 'Mariscos',
    ])->assertRedirect();

    expect($guest->fresh()->name)->toBe('Actualizado');
    expect($guest->fresh()->is_vip)->toBeTrue();
});

test('can delete a guest', function () {
    $guest = Guest::create([
        'restaurant_id' => $this->restaurant->id,
        'name'          => 'Para eliminar',
        'email'         => 'delete@example.com',
    ]);

    $this->delete("/guests/{$guest->uuid}")->assertRedirect();

    $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
});

test('guest show page is accessible', function () {
    $guest = Guest::create([
        'restaurant_id' => $this->restaurant->id,
        'name'          => 'Ver perfil',
        'email'         => 'show@example.com',
    ]);

    $this->get("/guests/{$guest->uuid}")->assertStatus(200);
});

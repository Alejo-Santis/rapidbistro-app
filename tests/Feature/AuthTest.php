<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login page is accessible', function () {
    $this->get('/login')->assertStatus(200);
});

test('authenticated user is redirected from login', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/login')->assertRedirect();
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
        ->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

test('login fails with invalid credentials', function () {
    User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('correct')]);

    $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors();
    $this->assertGuest();
});

test('user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/logout')->assertRedirect();
    $this->assertGuest();
});

test('unauthenticated user is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/reservations')->assertRedirect('/login');
    $this->get('/floor-map')->assertRedirect('/login');
});

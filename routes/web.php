<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

// ── Auth (sólo para invitados) ───────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// ── Rutas protegidas ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Reservaciones — rutas con UUID como route key
    Route::resource('reservations', ReservationController::class)->except(['show']);

    // Zonas — UUID como route key, modales en frontend
    Route::resource('zones', ZoneController::class)->except(['show', 'create', 'edit']);

    // Mesas — UUID como route key, modales en frontend
    Route::resource('tables', TableController::class)->except(['show', 'create', 'edit']);

    // Horarios del restaurante
    Route::resource('time-slots', TimeSlotController::class)
        ->except(['show', 'create', 'edit'])
        ->middleware('role:super-admin|admin');

    // Configuración del restaurante
    Route::get('/restaurant/settings', [RestaurantController::class, 'edit'])
        ->name('restaurant.edit')
        ->middleware('role:super-admin|admin');

    Route::put('/restaurant/settings', [RestaurantController::class, 'update'])
        ->name('restaurant.update')
        ->middleware('role:super-admin|admin');

    // Usuarios — solo admin/super-admin
    Route::resource('users', UserController::class)
        ->except(['show', 'create', 'edit'])
        ->middleware('role:super-admin|admin');
});

<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MaitreController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FloorMapController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\SpecialDateController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WalkInController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

// ── Portal público de reservas (sin auth) ───────────────────────────────────
Route::prefix('reservar')->name('booking.')->group(function () {
    Route::get('/',                         [PublicBookingController::class, 'index'])->name('index');
    Route::get('/disponibilidad',           [PublicBookingController::class, 'availability'])->name('availability');
    Route::post('/',                        [PublicBookingController::class, 'store'])->name('store');
    Route::get('/confirmacion/{code}',      [PublicBookingController::class, 'confirmation'])->name('confirmation');
    Route::get('/lista-espera',             [PublicBookingController::class, 'waitlistForm'])->name('waitlist');
    Route::post('/lista-espera',            [PublicBookingController::class, 'storeWaitlist'])->name('waitlist.store');
    Route::get('/lista-espera/gracias',     [PublicBookingController::class, 'waitlistSuccess'])->name('waitlist.success');
});

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

    // Mapa visual de mesas
    Route::get('/floor-map', [FloorMapController::class, 'index'])->name('floor-map.index');

    // Timeline de turnos
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');

    // Reservaciones — rutas con UUID como route key
    Route::resource('reservations', ReservationController::class)->except(['show']);
    Route::patch('/reservations/{reservation}/mark-no-show', [ReservationController::class, 'markNoShow'])->name('reservations.mark-no-show');
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');

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

    // Lista de espera — panel interno
    Route::get('/waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::patch('/waitlist/{waitlist}/notify', [WaitlistController::class, 'notify'])->name('waitlist.notify');
    Route::patch('/waitlist/{waitlist}/status', [WaitlistController::class, 'updateStatus'])->name('waitlist.status');
    Route::delete('/waitlist/{waitlist}', [WaitlistController::class, 'destroy'])->name('waitlist.destroy');

    // CRM de clientes
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::post('/guests/from-reservation', [GuestController::class, 'createFromReservation'])->name('guests.from-reservation');
    Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');

    // Lista de espera presencial (walk-in)
    Route::get('/walk-in', [WalkInController::class, 'index'])->name('walk-in.index');
    Route::post('/walk-in', [WalkInController::class, 'store'])->name('walk-in.store');
    Route::patch('/walk-in/{waitlist}/seat', [WalkInController::class, 'seat'])->name('walk-in.seat');
    Route::patch('/walk-in/{waitlist}/remove', [WalkInController::class, 'remove'])->name('walk-in.remove');

    // Eventos especiales y fechas bloqueadas
    Route::get('/special-dates', [SpecialDateController::class, 'index'])->name('special-dates.index');
    Route::post('/special-dates', [SpecialDateController::class, 'store'])->name('special-dates.store');
    Route::put('/special-dates/{specialDate}', [SpecialDateController::class, 'update'])->name('special-dates.update');
    Route::delete('/special-dates/{specialDate}', [SpecialDateController::class, 'destroy'])->name('special-dates.destroy');

    // Vista maître / staff
    Route::get('/maitre', [MaitreController::class, 'index'])->name('maitre.index');

    // Reportes y exportación PDF
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/export-excel', [ReportsController::class, 'exportExcel'])->name('reports.export-excel');

    // Usuarios — solo admin/super-admin
    Route::resource('users', UserController::class)
        ->except(['show', 'create', 'edit'])
        ->middleware('role:super-admin|admin');
});

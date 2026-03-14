<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('App', [
        'title' => 'Dashboard',
        'name' => 'John Doe',
    ]);
});

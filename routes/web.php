<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OficioController;
use App\Http\Controllers\PrioridadController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('areas', 'AreaController');
});

Route::resource('users', UserController::class);



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
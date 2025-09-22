<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OficioController;
use App\Http\Controllers\PrioridadController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Settings\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
});


Route::resource('areas', AreaController::class)->middleware(['auth', 'verified']);
Route::resource('users', UserController::class)->middleware(['auth', 'verified']);
Route::resource('oficios', OficioController::class)->middleware(['auth', 'verified']);
Route::resource('prioridades', PrioridadController::class)->middleware(['auth', 'verified']);

Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('auth');




require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

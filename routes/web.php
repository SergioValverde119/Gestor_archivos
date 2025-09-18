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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('oficios', [OficioController::class, 'index'])->name('oficios.index');
    Route::get('oficios/create', [OficioController::class, 'create'])->name('oficios.create');
    Route::post('oficios', [OficioController::class, 'store'])->name('oficios.store');
    Route::get('oficios/{oficio}', [OficioController::class, 'show'])->name('oficios.show');
    Route::get('oficios/{oficio}/edit', [OficioController::class, 'edit'])->name('oficios.edit');
    Route::put('oficios/{oficio}', [OficioController::class, 'update'])->name('oficios.update');
    Route::delete('oficios/{oficio}', [OficioController::class, 'destroy'])->name('oficios.destroy');

});



Route::resource('oficios', OficioController::class);
Route::resource('prioridades', PrioridadController::class);
Route::resource('areas', AreaController::class);
Route::resource('users', UserController::class);



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

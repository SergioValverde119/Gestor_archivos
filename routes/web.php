<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OficioController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Settings\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// --- RUTAS DE RECURSOS (SIN CAMBIOS) ---
Route::resource('areas', AreaController::class)->middleware(['auth', 'verified']);
Route::resource('users', UserController::class)->middleware(['auth', 'verified']);
// Prioridades ya no existe, así que la línea de abajo se puede eliminar
// Route::resource('prioridades', PrioridadController::class)->middleware(['auth', 'verified']);


// --- RUTAS DE OFICIOS (CORREGIDO) ---
Route::middleware(['auth', 'verified'])->prefix('oficios')->name('oficios.')->group(function () {
    // Rutas estándar
    Route::get('/', [OficioController::class, 'index'])->name('index');
    Route::get('/{oficio}', [OficioController::class, 'show'])->name('show');
    Route::get('/{oficio}/edit', [OficioController::class, 'edit'])->name('edit');
    Route::put('/{oficio}', [OficioController::class, 'update'])->name('update');
    Route::delete('/{oficio}', [OficioController::class, 'destroy'])->name('destroy');

    // Nuevas rutas para creación separada
    Route::get('/salida/crear', [OficioController::class, 'createSalida'])->name('createSalida');
    Route::post('/salida', [OficioController::class, 'storeSalida'])->name('storeSalida');
    
    Route::get('/entrada/registrar', [OficioController::class, 'createEntrada'])->name('createEntrada');
    Route::post('/entrada', [OficioController::class, 'storeEntrada'])->name('storeEntrada');
});


// --- RUTAS DE PERFIL (SIN CAMBIOS) ---
Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('auth');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

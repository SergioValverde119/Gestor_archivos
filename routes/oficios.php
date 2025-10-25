<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Oficios\OficioController;
use App\Http\Controllers\Oficios\OficioEntradaController;
use App\Http\Controllers\Oficios\OficioSalidaController;

// --- RUTAS DE OFICIOS (AHORA EN SU PROPIO ARCHIVO) ---
Route::middleware(['auth', 'verified'])->prefix('oficios')->name('oficios.')->group(function () {
    // Rutas de gestión (CRUD principal)
    Route::get('/', [OficioController::class, 'index'])->name('index');
    Route::get('/{oficio}', [OficioController::class, 'show'])->name('show');
    Route::get('/{oficio}/edit', [OficioController::class, 'edit'])->name('edit');
    Route::put('/{oficio}', [OficioController::class, 'update'])->name('update');
    Route::delete('/{oficio}', [OficioController::class, 'destroy'])->name('destroy');

    // Rutas para el flujo de ENTRADA
    Route::get('/entrada/registrar', [OficioEntradaController::class, 'create'])->name('createEntrada');
    Route::post('/entrada', [OficioEntradaController::class, 'store'])->name('storeEntrada');
    
    // Rutas para el flujo de SALIDA
    Route::get('/salida/crear', [OficioSalidaController::class, 'create'])->name('createSalida');
    Route::post('/salida', [OficioSalidaController::class, 'store'])->name('storeSalida');
});
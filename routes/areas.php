<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Areas\AreaController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Muestra la lista de todas las áreas
    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');

    // Muestra el formulario para crear una nueva área
    Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');

    // Almacena una nueva área en la base de datos
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');

    // Muestra un área específica (no lo estás usando, pero es buena práctica tenerla)
    Route::get('/areas/{area}', [AreaController::class, 'show'])->name('areas.show');

    // Muestra el formulario para editar un área
    Route::get('/areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');

    // Actualiza un área en la base de datos
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');

    // Elimina un área de la base de datos
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');
});

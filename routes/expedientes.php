<?php

use App\Http\Controllers\ExpedienteController;
use Illuminate\Support\Facades\Route;

// 1. Mostrar la lista de expedientes (El índice)
Route::get('/expedientes', [ExpedienteController::class, 'index'])
    ->name('expedientes.index');

// 2. Mostrar el formulario para crear un nuevo expediente
Route::get('/expedientes/create', [ExpedienteController::class, 'create'])
    ->name('expedientes.create');

// 3. Guardar el nuevo expediente en la base de datos
Route::post('/expedientes', [ExpedienteController::class, 'store'])
    ->name('expedientes.store');

// 4. Mostrar un expediente específico (La vista de detalle)
Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])
    ->name('expedientes.show');

// 5. Mostrar el formulario para editar un expediente
Route::get('/expedientes/{expediente}/edit', [ExpedienteController::class, 'edit'])
    ->name('expedientes.edit');

// 6. Actualizar un expediente específico en la base de datos
Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])
    ->name('expedientes.update');
// Nota: 'patch' también se registra para esta acción

// 7. Borrar un expediente específico
Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy'])
    ->name('expedientes.destroy');
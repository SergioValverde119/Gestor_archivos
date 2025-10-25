<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;

/*
|--------------------------------------------------------------------------
| Rutas del Dashboard
|--------------------------------------------------------------------------
|
| Aquí se define la ruta principal para el dashboard de la aplicación.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


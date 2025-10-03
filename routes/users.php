<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UsersController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Muestra la lista de todos los usuarios
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');

    // Muestra el formulario para crear un nuevo usuario
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');

    // Almacena un nuevo usuario en la base de datos
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');

    // Muestra un usuario específico
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');

    // Muestra el formulario para editar un usuario
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');

    // Actualiza un usuario en la base de datos
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');

    // Elimina un usuario de la base de datos
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
});
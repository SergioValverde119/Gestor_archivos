<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');







require __DIR__.'/expedientes.php';
require __DIR__.'/documentos.php';
require __DIR__.'/users.php';
require __DIR__.'/oficios.php';
require __DIR__.'/areas.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\DocumentoController;

/*
|--------------------------------------------------------------------------
| Rutas para la Gestión de Documentos
|--------------------------------------------------------------------------
|
| Aquí se definen todas las rutas para descargar, subir y eliminar
| documentos de forma segura.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
        // Ruta para la descarga segura de archivos
        Route::get('/{documento}/download', [DocumentoController::class, 'download'])->name('documentos.download');

        // Ruta para subir nuevos anexos a un oficio existente
        Route::post('/{oficio}/store', [DocumentoController::class, 'store'])->name('documentos.store');

        // Ruta para eliminar un documento específico (principal o anexo)
        Route::delete('/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');
    });

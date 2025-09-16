<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class pruebaController extends Controller
{
    /**
     * Muestra una página de prueba con datos pasados desde el backend.
     */
    public function prueba()
    {
        return Inertia::render('Pruebapagina', [
            'titulo' => '¡HolaAAAAAAAAAAAAAA desde el controlador de prueba!',
            'mensaje' => 'Esta es una página de prueba. Los datos fueron enviados desde el controlador.',
        ]);
    }
}
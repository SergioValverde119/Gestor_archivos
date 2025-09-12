<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Prioridad;

class PrioridadTest extends TestCase
{
    use RefreshDatabase; // Borra la base de datos después de cada prueba.

    public function test_prioridad_can_be_created(): void
    {
        // 1. Crear una nueva prioridad
        $prioridad = Prioridad::create([
            'nombre' => 'Urgente'
        ]);

        // 2. Afirmar que se guardó correctamente en la base de datos
        $this->assertDatabaseHas('prioridades', [
            'nombre' => 'Urgente'
        ]);
    }
}
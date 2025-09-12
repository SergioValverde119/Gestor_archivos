<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Prioridad;
use App\Models\Area;
use App\Models\User;

class OficioTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_oficio_can_be_created()
    {
        // 1. Crear las dependencias (Prioridad, Area, User)
        $prioridad = Prioridad::factory()->create();
        $area = Area::factory()->create();
        $user = User::factory()->create();

        // 2. Simular los datos del formulario de un nuevo oficio
        $oficioData = [
            'remitente' => 'Secretaría de Gobierno',
            'asunto' => 'Informe anual de actividades',
            'situacion' => 'Pendiente de revisión',
            'folio_interno' => 'SG/2025/001',
            'fecha_recepcion' => '2025-09-11',
            'fecha_limite' => null,
            'prioridad_id' => $prioridad->id,
            'area_id' => $area->id,
            'asignado_a_user_id' => $user->id,
            'status' => 'En espera',
        ];

        // 3. Simular el envío del formulario a la ruta de almacenamiento
        $response = $this->post('/oficios', $oficioData);

        // 4. Verificar que se haya guardado en la base de datos
        $this->assertDatabaseHas('oficios', $oficioData);
    }
}
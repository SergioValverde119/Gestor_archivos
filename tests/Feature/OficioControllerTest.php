<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Oficio;
use App\Models\Prioridad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OficioControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var \App\Models\User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario autenticado para cada test
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function puede_mostrar_el_listado_de_oficios()
    {
        // Crear datos de prueba
        Oficio::factory(15)->create();

        // Hacer la petición GET al index del controlador
        $response = $this->get(route('oficios.index'));

        // Aserciones
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Oficios/Index')
            ->has('oficios.data', 10) // Verifica que se devuelven 10 oficios paginados
            ->has('oficios.data.0.prioridad') // Verifica que las relaciones se cargaron
            ->has('oficios.data.0.area')
            ->has('oficios.data.0.asignado_a')
        );
    }

    /** @test */
    public function puede_crear_un_oficio_valido()
    {
        // Crear datos relacionados
        $prioridad = Prioridad::factory()->create();
        $area = Area::factory()->create();
        $userAsignado = User::factory()->create();

        // Datos del nuevo oficio
        $datosOficio = [
            'remitente' => $this->faker->company,
            'asunto' => $this->faker->sentence,
            'situacion' => 'Pendiente',
            'folio_interno' => $this->faker->unique()->randomNumber(5),
            'fecha_recepcion' => now()->subDays(2)->format('Y-m-d'),
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
            'prioridad_id' => $prioridad->id,
            'area_id' => $area->id,
            'asignado_a_user_id' => $userAsignado->id,
            'status' => 'En Proceso',
        ];

        // Hacer la petición POST al método store
        $response = $this->post(route('oficios.store'), $datosOficio);

        // Aserciones
        $response->assertRedirect(route('oficios.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('oficios', [
            'folio_interno' => $datosOficio['folio_interno'],
            'asignado_a_user_id' => $userAsignado->id,
        ]);
    }

    /** @test */
    public function no_puede_crear_un_oficio_con_datos_invalidos()
    {
        // Datos inválidos (folio_interno duplicado)
        $oficioExistente = Oficio::factory()->create();

        $datosInvalidos = [
            'folio_interno' => $oficioExistente->folio_interno,
            'asunto' => '', // Asunto vacío
            'prioridad_id' => 999, // Prioridad que no existe
        ];

        // Hacer la petición POST
        $response = $this->post(route('oficios.store'), $datosInvalidos);

        // Aserciones
        $response->assertSessionHasErrors(['folio_interno', 'asunto', 'prioridad_id']);
        $response->assertRedirect();
    }

    /** @test */
    public function puede_actualizar_un_oficio_existente()
    {
        // Crear un oficio existente
        $oficio = Oficio::factory()->create();

        // Datos para actualizar
        $nuevosDatos = [
            'folio_interno' => 'NUEVO-FOLIO-123',
            'asunto' => 'Asunto del Oficio Actualizado',
            'remitente' => $oficio->remitente,
            'situacion' => $oficio->situacion,
            'fecha_recepcion' => $oficio->fecha_recepcion->format('Y-m-d'),
            'fecha_limite' => $oficio->fecha_limite->format('Y-m-d'),
            'prioridad_id' => $oficio->prioridad_id,
            'area_id' => $oficio->area_id,
            'asignado_a_user_id' => $oficio->asignado_a_user_id,
            'status' => $oficio->status,
        ];

        // Hacer la petición PUT/PATCH al método update
        $response = $this->put(route('oficios.update', $oficio), $nuevosDatos);

        // Aserciones
        $response->assertRedirect(route('oficios.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('oficios', [
            'id' => $oficio->id,
            'folio_interno' => 'NUEVO-FOLIO-123',
            'asunto' => 'Asunto del Oficio Actualizado',
        ]);
    }

    /** @test */
    public function puede_eliminar_un_oficio()
    {
        // Crear un oficio para eliminar
        $oficio = Oficio::factory()->create();

        // Hacer la petición DELETE
        $response = $this->delete(route('oficios.destroy', $oficio));

        // Aserciones
        $response->assertRedirect(route('oficios.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('oficios', ['id' => $oficio->id]);
    }
}
<?php

namespace App\Http\Controllers\Oficios;

use App\Http\Controllers\Controller;
use App\Models\Oficio;
use App\Models\Area;
use App\Models\User;
use App\Services\OficioService; // Importamos el Servicio
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class OficioEntradaController extends Controller
{
    protected $oficioService;

    public function __construct(OficioService $oficioService)
    {
        $this->oficioService = $oficioService;
    }

    /**
     * Muestra el formulario para registrar un nuevo oficio de entrada.
     */
    public function create()
    {
        return Inertia::render('Oficios/CreateEntrada', [
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::where('role', 'operativo')->get(['id', 'name']),
            'searchableOficios' => Oficio::latest()->get(['id', 'folio_interno', 'folio_externo', 'folio_salida', 'asunto']),
            'allUsers' => User::all(['id', 'name']),
            'nextFolioInterno' => $this->oficioService->getNextFolio('interno'),
        ]);
    }
    
    /**
     * Almacena un nuevo oficio de entrada en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_ids' => 'required_without:oficio_respuesta_id|nullable|array',
            'area_ids.*' => 'exists:areas,id',
            'folio_externo' => 'required|string|max:255|unique:oficios',
            'remitente' => 'nullable|string|max:255',
            'asunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_recepcion' => 'required|date',
            'fecha_limite' => 'nullable|date',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'documento_principal' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'recibido_por_user_id' => 'required|exists:users,id',
            'oficio_respuesta_id' => 'nullable|exists:oficios,id',
            'tiene_turno_dgaf' => 'required|boolean',
            'folio_turno_dgaf' => 'required_if:tiene_turno_dgaf,true|nullable|string|max:255',
            'fecha_turno_dgaf' => 'required_if:tiene_turno_dgaf,true|nullable|date',
            'asignaciones' => 'nullable|array',
            'asignaciones.*.user_id' => 'required|exists:users,id',
            'asignaciones.*.permission' => ['required', Rule::in(['editor', 'visualizador'])],
        ]);

        try {
            $this->oficioService->crearOficioEntrada($validated, $request);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Error al guardar el oficio: ' . $e->getMessage()]);
        }

        return redirect()->route('oficios.createEntrada')->with('success', 'Oficio de Entrada registrado correctamente.');
    }
}
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

class OficioSalidaController extends Controller
{
    protected $oficioService;

    public function __construct(OficioService $oficioService)
    {
        $this->oficioService = $oficioService;
    }

    /**
     * Muestra el formulario para preparar un nuevo oficio de salida.
     */
    public function create()
    {
        return Inertia::render('Oficios/CreateSalida', [
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::where('role', 'operativo')->get(['id', 'name']),
            'searchableOficios' => Oficio::latest()->get(['id', 'folio_interno', 'folio_externo', 'folio_salida', 'asunto']),
            'nextFolioSalida' => $this->oficioService->getNextFolio('salida'),
        ]);
    }

    /**
     * Almacena un nuevo oficio de salida en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_ids' => 'required_without:oficio_respuesta_id|nullable|array',
            'area_ids.*' => 'exists:areas,id',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'oficio_respuesta_id' => 'nullable|exists:oficios,id',
            'documento_principal' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'asignaciones' => 'nullable|array',
            'asignaciones.*.user_id' => 'nullable|exists:users,id',
            'asignaciones.*.permission' => ['required_with:asignaciones.*.user_id', Rule::in(['editor', 'visualizador'])],
        ]);

        try {
            $this->oficioService->crearOficioSalida($validated, $request);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Error al generar el oficio: ' . $e->getMessage()]);
        }

        return redirect()->route('oficios.createSalida')->with('success', 'Oficio de Salida generado correctamente.');
    }
}
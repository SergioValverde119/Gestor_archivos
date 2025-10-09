<?php

namespace App\Http\Controllers\Oficios;

use App\Http\Controllers\Controller;
use App\Models\Oficio;
use App\Models\Expediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OficioController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra una lista de oficios aplicando la lógica de permisos, filtros y ordenamiento.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Oficio::query()->with([
            'expediente.areas', 
            'documentos',
            'recibidoPor:id,name',
            'permissions.user:id,name',
            'respuestaA:id,folio_interno'
        ]);

        // La lógica de filtrado por permisos se mantiene igual
        $query->where(function ($q) use ($user) {
            if (in_array($user->role, ['admin', 'director'])) {
                // Sin filtro, acceso total
            } elseif ($user->role === 'jefe_area' && $user->area_id) {
                $q->whereHas('expediente', function ($expedienteQuery) use ($user) {
                    $expedienteQuery->whereHas('areas', function ($areaQuery) use ($user) {
                        $areaQuery->where('areas.id', $user->area_id);
                    });
                });
            } else {
                $q->where(function ($permissionQuery) use ($user) {
                    $permissionQuery->whereHas('permissions', function ($pQuery) use ($user) {
                        $pQuery->where('user_id', $user->id)->where('permissible_type', Oficio::class);
                    })
                    ->orWhereHas('expediente', function ($expedienteQuery) use ($user) {
                        $expedienteQuery->whereHas('permissions', function ($pQuery) use ($user) {
                            $pQuery->where('user_id', $user->id)->where('permissible_type', Expediente::class);
                        });
                    });
                });
            }
        });

        // La lógica de búsqueda y filtros se mantiene igual
        $query->when($request->input('search'), function ($q, $search) { /* ... */ });
        // ... (resto de filtros y ordenamiento)
        
        $oficios = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    /**
     * Muestra los detalles de un oficio específico.
     */
    public function show(Oficio $oficio)
    {
        $this->authorize('view', $oficio);

        // --- CORRECCIÓN: Se expande la carga de relaciones para la vista de detalles ---
        $oficio->load([
            // Para el historial del expediente, carga otros oficios con sus datos clave
            'expediente.oficios' => function ($query) {
                $query->with(['recibidoPor:id,name', 'documentos'])
                      ->orderBy('created_at', 'desc');
            },
            'expediente.areas', // Para la tarjeta de asignaciones
            'documentos', // Para la lista de documentos del oficio actual
            'recibidoPor:id,name', // Para los detalles de entrada/salida
            'respuestaA:id,folio_interno,asunto', // Para saber a qué oficio responde
            'permissions.user:id,name' // Para la tarjeta de asignaciones
        ]);

        return Inertia::render('Oficios/Show', [
            'oficio' => $oficio,
        ]);
    }

    /**
     * Muestra el formulario para editar un oficio.
     */
    public function edit(Oficio $oficio)
    {
        $this->authorize('update', $oficio);

        $oficio->load(['expediente']);
        
        return Inertia::render('Oficios/Edit', [
            'oficio' => $oficio,
        ]);
    }

    /**
     * Actualiza un oficio existente.
     */
    public function update(Request $request, Oficio $oficio)
    {
        $this->authorize('update', $oficio);
        
        $validated = $request->validate([
            'asunto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'resolucion' => 'nullable|string',
            'oficio_respuesta_id' => 'nullable|exists:oficios,id',
        ]);

        $oficio->update($validated);
        
        return redirect()->route('oficios.index')->with('success', 'Oficio actualizado correctamente.');
    }

    /**
     * Elimina un oficio.
     */
    public function destroy(Oficio $oficio)
    {
        $this->authorize('delete', $oficio);

        DB::transaction(function () use ($oficio) {
            foreach ($oficio->documentos as $documento) {
                Storage::disk('public')->delete($documento->ruta_almacenamiento);
            }
            $oficio->delete();
        });

        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }
}

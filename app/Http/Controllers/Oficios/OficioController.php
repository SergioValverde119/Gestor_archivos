<?php

namespace App\Http\Controllers\Oficios;

use App\Http\Controllers\Controller;
use App\Models\Oficio;
use App\Models\Area;
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
        
        // --- CORRECCIÓN 1: Se almacenan los filtros validados en una variable ---
        $filters = $request->validate([
            'sort' => 'nullable|string|in:folio,asunto,status,prioridad,fechaRegistro,fechaRecepcion,fechaLimite',
            'direction' => 'nullable|string|in:asc,desc',
            'tiene_turno_dgaf' => 'nullable|in:true,false',
            'tipo' => ['nullable', Rule::in(['entrada', 'salida'])],
            'per_page' => 'nullable|integer|in:8,15,25,50',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'recepcion_from' => 'nullable|date',
            'recepcion_to' => 'nullable|date|after_or_equal:recepcion_from',
            'limite_from' => 'nullable|date',
            'limite_to' => 'nullable|date|after_or_equal:limite_from',
            'area_ids' => 'nullable|array',
            'area_ids.*' => 'integer|exists:areas,id',
            'search' => 'nullable|string|max:255',
        ]);

        $query = Oficio::query()->with([
            'expediente.areas', 
            'documentos',
            'recibidoPor:id,name',
            'permissions.user:id,name',
            'respuestaA'
        ]);

        // La lógica de filtrado por permisos se mantiene aquí
        $query->where(function ($q) use ($user) {
            if (in_array($user->role, ['admin', 'director'])) {
                // Sin filtro, acceso total
            } elseif ($user->role === 'jefe_area' && $user->area_id) {
                $q->whereHas('expediente.areas', fn($aq) => $aq->where('areas.id', $user->area_id));
            } else {
                $q->where(function ($permissionQuery) use ($user) {
                    $permissionQuery->whereHas('permissions', fn($pq) => $pq->where('user_id', $user->id)->where('permissible_type', Oficio::class))
                        ->orWhereHas('expediente.permissions', fn($pq) => $pq->where('user_id', $user->id)->where('permissible_type', Expediente::class));
                });
            }
        });

        // --- CORRECCIÓN 2: Se pasan los $filters validados (no $request->all()) ---
        $oficios = $query->filter($filters)
            // --- CORRECCIÓN 3: El orden por defecto solo se aplica si NO se pide uno específico ---
            ->when(!isset($filters['sort']), fn($q) => $q->latest('created_at'))
            ->paginate($filters['per_page'] ?? 8)
            ->withQueryString();

        

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            // --- CORRECCIÓN 4: Se devuelven los $filters validados ---
            'filters' => $filters,
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    /**
     * Muestra los detalles de un oficio específico.
     */
    public function show(Oficio $oficio)
    {
       // $this->authorize('view', $oficio);

        $oficio->load([
            'expediente.areas',
            'expediente.oficios' => fn($q) => $q->with([
                'documentos', 
                'recibidoPor:id,name' // Añade esto
            ])->orderBy('created_at'),
            'documentos',
            'recibidoPor:id,name',
            'permissions.user:id,name',
            'respuestaA',            
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

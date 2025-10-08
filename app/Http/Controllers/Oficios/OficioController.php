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

        // La lógica de filtrado por permisos no cambia
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

        // --- LÓGICA DE BÚSQUEDA Y FILTROS ---
        $request->validate([
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
        ]);

        $query->when($request->input('search'), function ($q, $search) {
            $q->where(function($sub) use ($search) {
                $sub->where('folio_externo', 'like', "%{$search}%")
                    ->orWhere('folio_salida', 'like', "%{$search}%")
                    ->orWhere('asunto', 'like', "%{$search}%");
            });
        });

        if ($request->has('tiene_turno_dgaf') && $request->input('tiene_turno_dgaf') !== null) {
             $query->where('tiene_turno_dgaf', $request->input('tiene_turno_dgaf') === 'true');
        }
        
        $query->when($request->input('tipo'), fn($q, $tipo) => $q->where('tipo', $tipo));
        
        // Filtros de Rango de Fechas
        $query->when($request->input('date_from'), fn($q, $date) => $q->whereNotNull('created_at')->whereDate('created_at', '>=', $date));
        $query->when($request->input('date_to'), fn($q, $date) => $q->whereNotNull('created_at')->whereDate('created_at', '<=', $date));
        $query->when($request->input('recepcion_from'), fn($q, $date) => $q->whereNotNull('fecha_recepcion')->whereDate('fecha_recepcion', '>=', $date));
        $query->when($request->input('recepcion_to'), fn($q, $date) => $q->whereNotNull('fecha_recepcion')->whereDate('fecha_recepcion', '<=', $date));
        $query->when($request->input('limite_from'), fn($q, $date) => $q->whereNotNull('fecha_limite')->whereDate('fecha_limite', '>=', $date));
        $query->when($request->input('limite_to'), fn($q, $date) => $q->whereNotNull('fecha_limite')->whereDate('fecha_limite', '<=', $date));

        // Filtro por Áreas Involucradas

        $query->when($request->input('area_ids'), function ($q, $areaIds) {
            $q->whereHas('expediente.areas', function ($areaQuery) use ($areaIds) {
                $areaQuery->whereIn('areas.id', $areaIds);
            });
        });

        
        $sortColumn = $request->input('sort');
        $sortDirection = $request->input('direction', 'asc');

        $columnMap = [
            'folio' => 'folio_externo',
            'asunto' => 'asunto',
            'status' => 'status',
            'prioridad' => 'prioridad',
            'fechaRegistro' => 'created_at',
            'fechaRecepcion' => 'fecha_recepcion',
            'fechaLimite' => 'fecha_limite',
        ];

        if ($sortColumn && isset($columnMap[$sortColumn])) {
            $query->orderBy($columnMap[$sortColumn], $sortDirection);
        } else {
            $query->latest('created_at');
        }
        
        $perPage = $request->input('per_page', 8);
        $oficios = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            'filters' => $request->only(['search', 'sort', 'direction', 'tiene_turno_dgaf','tipo','per_page', 'date_from', 'date_to', 'recepcion_from', 'recepcion_to', 'limite_from', 'limite_to']),
        ]);
    }

    /**
     * Muestra los detalles de un oficio específico.
     */
    public function show(Oficio $oficio)
    {
        $this->authorize('view', $oficio);

        $oficio->load([
            'expediente.areas',
            'expediente.oficios' => fn($q) => $q->with('documentos')->orderBy('created_at'),
            'documentos',
            'recibidoPor:id,name',
            'respuestaA',
            'permissions.user:id,name'
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

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
            'sort' => 'nullable|string|in:folio,asunto,status,prioridad,fechaRegistro',
            'direction' => 'nullable|string|in:asc,desc',
            'tiene_turno_dgaf' => 'nullable|in:true,false',
            'tipo' => ['nullable', Rule::in(['entrada', 'salida'])],
            'per_page' => 'nullable|integer|in:8,15,25,50',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
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

    
        
        $query->when($request->input('tipo'), function ($q, $tipo) {
            $q->where('tipo', $tipo);
        });

        $query->when($request->input('date_from'), function ($q, $dateFrom) {
        $q->whereDate('created_at', '>=', $dateFrom);
        });

        $query->when($request->input('date_to'), function ($q, $dateTo) {
            $q->whereDate('created_at', '<=', $dateTo);
        });

        $sortColumn = $request->input('sort');
        $sortDirection = $request->input('direction', 'asc');

        $columnMap = [
            'folio' => 'folio_externo', // Puedes decidir por cuál ordenar por defecto
            'asunto' => 'asunto',
            'status' => 'status',
            'prioridad' => 'prioridad',
            'fechaRegistro' => 'created_at',
        ];

        if ($sortColumn && isset($columnMap[$sortColumn])) {
            $query->orderBy($columnMap[$sortColumn], $sortDirection);
        } else {
            $query->latest('created_at');
        }
        
        $perPage = $request->input('per_page', 8); // Por defecto, 8 registros
        $oficios = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            'filters' => $request->only(['search', 'sort', 'direction', 'tiene_turno_dgaf','tipo','per_page', 'date_from', 'date_to']),
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
            'expediente.oficios' => fn($q) => $q->with('documentos')->orderBy('created_at'), // También se simplifica aquí
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
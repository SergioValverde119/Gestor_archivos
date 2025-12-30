<?php

namespace App\Http\Controllers\Oficios;

use App\Http\Controllers\Controller;
use App\Models\Oficio;
use App\Models\Area;
use App\Models\User;
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
     * Muestra la lista de oficios con filtros y paginación.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Validación de filtros
        $filters = $request->validate([
            'sort' => 'nullable|string',
            'direction' => 'nullable|string|in:asc,desc',
            'tiene_turno_dgaf' => 'nullable|in:true,false',
            'tipo' => ['nullable', Rule::in(['entrada', 'salida'])],
            'per_page' => 'nullable|integer|in:8,15,25,50',
            'search' => 'nullable|string|max:255',
            // Filtros de fecha
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'recepcion_from' => 'nullable|date',
            'recepcion_to' => 'nullable|date|after_or_equal:recepcion_from',
            'limite_from' => 'nullable|date',
            'limite_to' => 'nullable|date|after_or_equal:limite_from',
            // Filtro de áreas (Buscaremos en el expediente)
            'area_ids' => 'nullable|array',
            'area_ids.*' => 'integer|exists:areas,id',
        ]);

        // 2. Consulta Base con relaciones necesarias
        $query = Oficio::query()->with([
            // Cargamos las áreas A TRAVÉS del Expediente (Según tu diseño)
            'expediente.areas', 
            'documentos',
            'recibidoPor:id,name',
            'permissions.user:id,name',
            'respuestaA'
        ]);

        // 3. Lógica de Permisos / Roles
        $query->where(function ($q) use ($user) {
            // Admin y Director ven todo
            if (in_array($user->role, ['admin', 'director'])) {
                return;
            } 
            // Jefes de Área ven lo que corresponda a su área (vía Expediente)
            elseif ($user->role === 'jefe_area' && $user->area_id) {
                $q->whereHas('expediente.areas', fn($aq) => $aq->where('areas.id', $user->area_id));
            } 
            // Operativos ven lo asignado o lo que registraron
            else {
                $q->where(function ($permissionQuery) use ($user) {
                    $permissionQuery->whereHas('permissions', fn($pq) => $pq->where('user_id', $user->id))
                        ->orWhereHas('expediente.permissions', fn($pq) => $pq->where('user_id', $user->id))
                        ->orWhere('recibido_por_user_id', $user->id);
                });
            }
        });

        // 4. Aplicación de Filtros Específicos
        
        // Búsqueda de texto
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('asunto', 'like', "%{$term}%")
                  ->orWhere('folio_interno', 'like', "%{$term}%")
                  ->orWhere('folio_externo', 'like', "%{$term}%")
                  ->orWhere('folio_salida', 'like', "%{$term}%")
                  ->orWhere('remitente', 'like', "%{$term}%")
                  ->orWhere('destinatario', 'like', "%{$term}%");
            });
        }

        // Filtro por Tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro DGAF
        if ($request->filled('tiene_turno_dgaf')) {
            $query->where('tiene_turno_dgaf', $request->tiene_turno_dgaf === 'true');
        }

        // 5. Ordenamiento y Paginación
        $sortColumn = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        
        // Mapeo simple para columnas que no existen directo en la BD
        if ($sortColumn === 'fechaRegistro') $sortColumn = 'created_at';
        if ($sortColumn === 'folio') $sortColumn = 'folio_interno'; // O lógica personalizada

        $oficios = $query->orderBy($sortColumn, $sortDirection)
            ->paginate($filters['per_page'] ?? 8)
            ->withQueryString();

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            'filters' => $filters,
            'areas' => Area::select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Muestra el detalle de un oficio.
     */
    public function show(Oficio $oficio)
    {
        // $this->authorize('view', $oficio);

        $oficio->load([
            'expediente.areas',
            'expediente.oficios' => fn($q) => $q->with(['documentos', 'recibidoPor:id,name'])->orderBy('created_at'),
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
     * Formulario de edición.
     */
    public function edit(Oficio $oficio)
    {
        // $this->authorize('update', $oficio);
        
        $oficio->load(['expediente']);

        return Inertia::render('Oficios/Edit', [
            'oficio' => $oficio,
            // Listas para los Selects
            'areas' => Area::select('id', 'nombre')->orderBy('nombre')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'expedientes' => Expediente::select('id', 'titulo', 'numero_expediente')->latest()->get(),
            // Para seleccionar a qué oficio responde (si es salida)
            'oficios_posibles_respuesta' => Oficio::where('tipo', 'entrada')
                                            ->where('id', '!=', $oficio->id)
                                            ->select('id', 'folio_interno', 'asunto')
                                            ->latest()
                                            ->limit(50)
                                            ->get(),
        ]);
    }

    /**
     * Actualiza el oficio en la BD.
     */
    public function update(Request $request, Oficio $oficio)
    {
        // $this->authorize('update', $oficio);

        // 1. Reglas Comunes
        $rules = [
            'asunto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:50',
            'expediente_id' => 'nullable|exists:expedientes,id',
            'recibido_por_user_id' => 'nullable|exists:users,id',
        ];

        // 2. Reglas Condicionales
        if ($oficio->tipo === 'entrada') {
            $rules = array_merge($rules, [
                'folio_externo' => 'nullable|string|max:100',
                'remitente' => 'nullable|string|max:255',
                'fecha_recepcion' => 'nullable|date',
                'fecha_limite' => 'nullable|date',
                'tiene_turno_dgaf' => 'boolean',
                'folio_turno_dgaf' => 'nullable|required_if:tiene_turno_dgaf,true|string|max:100',
                'fecha_turno_dgaf' => 'nullable|required_if:tiene_turno_dgaf,true|date',
            ]);
        } else {
            $rules = array_merge($rules, [
                'destinatario' => 'required|string|max:255',
                'oficio_respuesta_id' => 'nullable|exists:oficios,id',
            ]);
        }

        $validated = $request->validate($rules);

        // 3. Limpieza de datos (DGAF)
        if ($oficio->tipo === 'entrada' && empty($request->tiene_turno_dgaf)) {
            $validated['folio_turno_dgaf'] = null;
            $validated['fecha_turno_dgaf'] = null;
            $validated['tiene_turno_dgaf'] = false;
        }

        // 4. Actualización
        // NOTA: No hacemos sync de áreas porque las áreas dependen del expediente seleccionado.
        $oficio->update($validated);
        
        return redirect()->back()->with('success', 'Oficio actualizado correctamente.');
    }

    /**
     * Elimina el oficio y sus archivos.
     */
    public function destroy(Oficio $oficio)
    {
        // $this->authorize('delete', $oficio);

        DB::transaction(function () use ($oficio) {
            // Borrar archivos físicos
            foreach ($oficio->documentos as $documento) {
                if (Storage::disk('public')->exists($documento->ruta_almacenamiento)) {
                    Storage::disk('public')->delete($documento->ruta_almacenamiento);
                }
            }
            // Borrar registro
            $oficio->delete();
        });

        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }
}
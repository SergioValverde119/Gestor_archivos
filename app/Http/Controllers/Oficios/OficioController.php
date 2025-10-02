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
     * Muestra una lista de oficios aplicando la lógica de permisos.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // --- CORRECCIÓN: Se cargan también las áreas del expediente para mostrarlas en la tabla ---
        $query = Oficio::query()->with([
            'expediente.areas', 
            'documentoPrincipal', 
            'recibidoPor:id,name'
        ]);

        // Aplicar filtros de permisos basados en el rol del usuario
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
                        $pQuery->where('user_id', $user->id)
                            ->where('permissible_type', Oficio::class);
                    })
                        ->orWhereHas('expediente', function ($expedienteQuery) use ($user) {
                            $expedienteQuery->whereHas('permissions', function ($pQuery) use ($user) {
                                $pQuery->where('user_id', $user->id)
                                    ->where('permissible_type', Expediente::class);
                            });
                        });
                });
            }
        });

        // Aplicar filtros de búsqueda de la interfaz
        $query->when($request->input('search'), function ($q, $search) {
            $q->where('folio_externo', 'like', "%{$search}%")
                ->orWhere('folio_salida', 'like', "%{$search}%")
                ->orWhere('asunto', 'like', "%{$search}%");
        });

        $oficios = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Oficios/Index', [
            'oficios' => $oficios,
            'filters' => $request->only(['search']),
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
            'expediente.oficios' => function ($query) {
                $query->with('documentoPrincipal')->orderBy('created_at');
            },
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
            // Aquí podrías necesitar enviar datos adicionales para los selects del formulario
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
            // Añadir aquí cualquier otro campo que se pueda editar
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
            $oficio->delete(); // Las relaciones en cascada se encargan del resto
        });

        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }
}
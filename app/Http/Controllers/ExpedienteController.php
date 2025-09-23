<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ExpedienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Expediente::query()->with('areas'); // Carga las áreas relacionadas

        // Aplicar filtros de permisos basados en el rol del usuario
        $query->where(function ($q) use ($user) {
            // 1. Si es admin o director, puede ver todo.
            if ($user->role === 'admin' || $user->role === 'director') {
                // Sin filtro, acceso total.
            }
            // 2. Si es jefe de área, ve los expedientes de su área.
            elseif ($user->role === 'jefe_area' && $user->area_id) {
                $q->whereHas('areas', function ($areaQuery) use ($user) {
                    $areaQuery->where('areas.id', $user->area_id);
                });
            }
            // 3. Si es operativo, ve los expedientes para los que tiene permiso explícito.
            else {
                $q->whereHas('permissions', function ($pQuery) use ($user) {
                    $pQuery->where('user_id', $user->id)
                           ->where('permissible_type', Expediente::class);
                });
            }
        });

        // Aplicar filtros de búsqueda de la interfaz
        $query->when($request->input('search'), function ($q, $search) {
            $q->where('titulo', 'like', "%{$search}%")
              ->orWhere('numero_expediente', 'like', "%{$search}%");
        });

        $expedientes = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Expedientes/Index', [
            'expedientes' => $expedientes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Expedientes/Create', [
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_expediente' => 'required|string|max:255|unique:expedientes',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
        ]);

        $expediente = DB::transaction(function () use ($validated) {
            $expediente = Expediente::create($validated);
            $expediente->areas()->sync($validated['area_ids']);
            return $expediente;
        });

        return redirect()->route('expedientes.index')->with('success', 'Expediente creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expediente $expediente)
    {
        // Política de autorización (se implementaría en un Policy)
        // $this->authorize('view', $expediente);

        $expediente->load(['areas', 'oficios.documentoPrincipal', 'oficios.recibidoPor:id,name']);

        return Inertia::render('Expedientes/Show', [
            'expediente' => $expediente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expediente $expediente)
    {
        // $this->authorize('update', $expediente);
        
        $expediente->load('areas');

        return Inertia::render('Expedientes/Edit', [
            'expediente' => $expediente,
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expediente $expediente)
    {
        // $this->authorize('update', $expediente);

        $validated = $request->validate([
            'numero_expediente' => ['required', 'string', 'max:255', Rule::unique('expedientes')->ignore($expediente->id)],
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
        ]);

        DB::transaction(function () use ($validated, $expediente) {
            $expediente->update($validated);
            $expediente->areas()->sync($validated['area_ids']);
        });
        
        return redirect()->route('expedientes.index')->with('success', 'Expediente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expediente $expediente)
    {
        // $this->authorize('delete', $expediente);

        // Prevenir borrado si tiene oficios asociados
        if ($expediente->oficios()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar un expediente que contiene oficios.');
        }

        $expediente->delete();

        return redirect()->route('expedientes.index')->with('success', 'Expediente eliminado correctamente.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Oficio;
use App\Models\Prioridad;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class OficioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los oficios con sus relaciones
        $oficios = Oficio::with(['prioridad', 'area', 'asignadoA'])
            ->latest()
            ->paginate(10);

        // Renderizar la vista de Inertia con los oficios
        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener datos necesarios para los selectores del formulario
        $prioridades = Prioridad::all();
        $areas = Area::all();
        $users = User::all();

        return Inertia::render('Oficios/Create', [
            'prioridades' => $prioridades,
            'areas' => $areas,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'remitente' => 'required|string|max:255',
            'asunto' => 'required|string|max:255',
            'situacion' => 'required|string',
            'folio_interno' => 'required|string|max:255|unique:oficios,folio_interno',
            'fecha_recepcion' => 'required|date',
            'fecha_limite' => 'nullable|date|after_or_equal:fecha_recepcion',
            'prioridad_id' => 'required|exists:prioridades,id',
            'area_id' => 'required|exists:areas,id',
            'asignado_a_user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        Oficio::create($validatedData);

        return redirect()->route('oficios.index')->with('success', 'Oficio creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Oficio $oficio)
    {
        // Cargar las relaciones del oficio
        $oficio->load(['prioridad', 'area', 'asignadoA']);

        return Inertia::render('Oficios/Show', [
            'oficio' => $oficio,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Oficio $oficio)
    {
        // Cargar las relaciones para el formulario de edición
        $oficio->load(['prioridad', 'area', 'asignadoA']);

        return Inertia::render('Oficios/Edit', [
            'oficio' => $oficio,
            'prioridades' => Prioridad::all(),
            'areas' => Area::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oficio $oficio)
    {
        $validatedData = $request->validate([
            'remitente' => 'required|string|max:255',
            'asunto' => 'required|string|max:255',
            'situacion' => 'required|string',
            'folio_interno' => [
                'required',
                'string',
                'max:255',
                Rule::unique('oficios')->ignore($oficio->id),
            ],
            'fecha_recepcion' => 'required|date',
            'fecha_limite' => 'nullable|date|after_or_equal:fecha_recepcion',
            'prioridad_id' => 'required|exists:prioridades,id',
            'area_id' => 'required|exists:areas,id',
            'asignado_a_user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        $oficio->update($validatedData);

        return redirect()->route('oficios.index')->with('success', 'Oficio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oficio $oficio)
    {
        $oficio->delete();

        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }
}
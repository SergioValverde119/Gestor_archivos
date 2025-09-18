<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Oficio;
use App\Models\Prioridad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OficioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $oficios = Oficio::with(['prioridad', 'area', 'asignadoA'])
            ->paginate(10);

        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
            'prioridades' => Prioridad::all(),
            'areas' => Area::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Oficios/create', [
            'prioridades' => Prioridad::all(),
            'areas' => Area::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'folio_oficio' => ['required', 'string', 'max:255', 'unique:oficios,folio_oficio'],
            'folio_interno' => ['nullable', 'string', 'max:255', 'unique:oficios,folio_interno'],
            'remitente' => ['nullable', 'string', 'max:255'],
            'asunto' => ['nullable', 'string'],
            'fecha_recepcion' => ['nullable', 'date'],
            'fecha_limite' => ['nullable', 'date'],
            'situacion' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'prioridad_id' => ['nullable', 'exists:prioridades,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'asignado_a_user_id' => ['nullable', 'exists:users,id'],
        ]);

        Oficio::create($validatedData);

        return redirect()->route('oficios.index')->with('success', 'Oficio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Oficio $oficio)
    {
        // Cargamos la relación del documento para que esté disponible en el frontend
        $oficio->load('documento');

        return Inertia::render('Oficios/show', [
            'oficio' => $oficio,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Oficio $oficio)
    {
        $oficio->load('prioridad', 'area', 'asignadoA');

        return Inertia::render('Oficios/edit', [
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
            'folio_oficio' => ['required', 'string', 'max:255', Rule::unique('oficios')->ignore($oficio->id)],
            'folio_interno' => ['nullable', 'string', 'max:255', Rule::unique('oficios')->ignore($oficio->id)],
            'remitente' => ['nullable', 'string', 'max:255'],
            'asunto' => ['nullable', 'string'],
            'fecha_recepcion' => ['nullable', 'date'],
            'fecha_limite' => ['nullable', 'date'],
            'situacion' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'prioridad_id' => ['nullable', 'exists:prioridades,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'asignado_a_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $oficio->update($validatedData);

        return redirect()->route('oficios.index')->with('success', 'Oficio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oficio $oficio)
    {
        $oficio->delete();
        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado exitosamente.');
    }
}
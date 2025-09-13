<?php

namespace App\Http\Controllers;

use App\Models\Prioridad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PrioridadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prioridades = Prioridad::latest()->paginate(10);

        return Inertia::render('Prioridades/Index', [
            'prioridades' => $prioridades,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Prioridades/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:prioridades,nombre',
        ]);

        Prioridad::create($validatedData);

        return redirect()->route('prioridades.index')->with('success', 'Prioridad creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prioridad $prioridad)
    {
        return Inertia::render('Prioridades/Show', [
            'prioridad' => $prioridad,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prioridad $prioridad)
    {
        return Inertia::render('Prioridades/Edit', [
            'prioridad' => $prioridad,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prioridad $prioridad)
    {
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('prioridades')->ignore($prioridad->id),
            ],
        ]);

        $prioridad->update($validatedData);

        return redirect()->route('prioridades.index')->with('success', 'Prioridad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prioridad $prioridad)
    {
        // Se valida que la prioridad no esté en uso por un oficio
        if ($prioridad->oficios()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una prioridad con oficios asociados.');
        }
        
        $prioridad->delete();

        return redirect()->route('prioridades.index')->with('success', 'Prioridad eliminada correctamente.');
    }
}
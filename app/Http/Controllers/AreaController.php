<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $areas = Area::latest()->paginate(10);

        return Inertia::render('Areas/Index', [
           'areas' => $areas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Areas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre',
        ]);

        Area::create($validatedData);

        return redirect()->route('areas.index')->with('success', 'Área creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return Inertia::render('Areas/Show', [
            'area' => $area,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        return Inertia::render('Areas/Edit', [
            'area' => $area,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->ignore($area->id),
            ],
        ]);

        $area->update($validatedData);

        return redirect()->route('areas.index')->with('success', 'Área actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        // Validar que el área no esté en uso por un expediente o un usuario jefe.
        if ($area->expedientes()->exists() || $area->jefes()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar un área que está asignada a expedientes o a un jefe de área.');
        }

        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Área eliminada correctamente.');
    }
}
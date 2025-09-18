<?php

namespace App\Http\Controllers;

use App\Http\Requests\OficioStoreRequest;
use App\Models\Oficio;
use App\Models\Documento; // Nuevo: Importa el modelo Documento
use App\Models\Prioridad;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Nuevo: Para el almacenamiento de archivos

class OficioController extends Controller
{
    /**
     * Muestra la lista de oficios.
     */
    public function index()
    {
        // Carga los oficios paginados con sus relaciones
        $oficios = Oficio::with(['prioridad', 'area', 'asignadoA'])->paginate(10);
        
        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo oficio.
     */
    public function create()
    {
        // Obtiene las listas de prioridades, áreas y usuarios
        $prioridades = Prioridad::all(['id', 'nombre']);
        $areas = Area::all(['id', 'nombre']);
        $users = User::all(['id', 'name']);

        return Inertia::render('Oficios/create', [
            'prioridades' => $prioridades,
            'areas' => $areas,
            'users' => $users,
        ]);
    }

    /**
     * Almacena un oficio recién creado en el almacenamiento.
     */
    public function store(OficioStoreRequest $request)
    {
        // Los datos ya están validados en este punto gracias a OficioStoreRequest
        $validatedData = $request->validated();
        
        // Crea el nuevo oficio con los datos validados
        $oficio = Oficio::create($validatedData);

        // Si un archivo fue adjuntado, se guarda y se crea un registro en la tabla 'documento'.
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            
            // Guarda el archivo y obtiene la ruta
            $filePath = $file->store('oficios', 'public');
            
            // Crea el nuevo registro en la tabla documento
            Documento::create([
                'oficio_id' => $oficio->id,
                'nombre_documento' => $file->getClientOriginalName(),
                'ruta_almacenamiento' => $filePath,
                'tipo_documento' => $file->getMimeType(),
            ]);
        }

        return redirect()->route('oficios.create')->with('success', 'Oficio y documento guardados exitosamente.');
    }

    /**
     * Muestra los detalles de un oficio específico.
     */
    public function show(Oficio $oficio)
    {
        // Carga las relaciones para mostrarlas en la vista de detalles
        $oficio->load(['prioridad', 'area', 'asignadoA']);

        return Inertia::render('Oficios/show', [
            'oficio' => $oficio,
        ]);
    }

    /**
     * Muestra el formulario para editar un oficio específico.
     */
    public function edit(Oficio $oficio)
    {
        // Carga las relaciones para precargar el formulario
        $prioridades = Prioridad::all(['id', 'nombre']);
        $areas = Area::all(['id', 'nombre']);
        $users = User::all(['id', 'name']);
        
        return Inertia::render('Oficios/edit', [
            'oficio' => $oficio,
            'prioridades' => $prioridades,
            'areas' => $areas,
            'users' => $users,
        ]);
    }

    /**
     * Actualiza un oficio específico en el almacenamiento.
     */
    public function update(Request $request, Oficio $oficio)
    {
        // Se valida la petición. La regla 'unique' ignora el oficio actual.
        $validatedData = $request->validate([
            'folio_oficio' => ['required', 'string', 'max:255', Rule::unique('oficios')->ignore($oficio->id)],
            'remitente' => 'required|string|max:255',
            'asunto' => 'required|string',
            'situacion' => 'nullable|string',
            'folio_interno' => 'nullable|string|max:255',
            'fecha_recepcion' => 'required|date',
            'fecha_limite' => 'required|date',
            'prioridad_id' => 'required|exists:prioridades,id',
            'area_id' => 'required|exists:areas,id',
            'asignado_a_user_id' => 'required|exists:users,id',
            'status' => 'required|string',
        ]);

        // Actualiza el oficio con los datos validados
        $oficio->update($validatedData);

        return redirect()->route('oficios.index')->with('success', 'Oficio actualizado exitosamente.');
    }

    /**
     * Elimina un oficio específico del almacenamiento.
     */
    public function destroy(Oficio $oficio)
    {
        $oficio->delete();

        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado exitosamente.');
    }
}
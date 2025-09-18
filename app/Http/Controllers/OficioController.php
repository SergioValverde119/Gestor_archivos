<?php

namespace App\Http\Controllers;

use App\Http\Requests\OficioStoreRequest;
use App\Models\Oficio;
use App\Models\Documento;
use App\Models\Prioridad;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class OficioController extends Controller
{
    /**
     * Muestra la lista de oficios.
     * Ahora recibe un parámetro 'field' para el campo de búsqueda y carga el documento.
     */
    public function index(Request $request)
    {
        // Obtiene los parámetros de búsqueda y campo desde la URL
        $search = $request->query('search');
        $field = $request->query('field', 'folio_oficio'); // Por defecto, busca por 'folio_oficio'

        // Construye la consulta para los oficios
        $query = Oficio::with(['prioridad', 'area', 'asignadoA', 'documento']);

        // Si hay un término de búsqueda, aplica el filtro al campo especificado
        if ($search) {
            $query->where($field, 'like', "%{$search}%");
        }

        // Carga los oficios paginados con sus relaciones y mantiene los parámetros en la URL
        $oficios = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
            'search' => $search, // Pasa el término de búsqueda a la vista
            'field' => $field,   // Pasa el campo de búsqueda a la vista
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo oficio.
     */
    public function create()
    {
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
        $validatedData = $request->validated();
        
        $oficio = Oficio::create($validatedData);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            
            $filePath = $file->store('oficios', 'public');
            
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
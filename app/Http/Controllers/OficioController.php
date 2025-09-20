<?php

namespace App\Http\Controllers;

use App\Models\Oficio;
use App\Models\Prioridad;
use App\Models\Area;
use App\Models\User;
use App\Models\Documento; // Asegúrate de tener este modelo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class OficioController extends Controller
{
    /**
     * Muestra una lista de oficios con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        // Aplicar filtros de búsqueda
        $oficios = Oficio::query()
            ->with('documento') // Carga la relación con el documento para evitar N+1 queries
            ->when($request->input('search'), function ($query, $search) use ($request) {
                $field = $request->input('field', 'folio_oficio');
                $query->where($field, 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Mantiene los parámetros de búsqueda en la paginación

        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
            // Devuelve los filtros al frontend para que los inputs mantengan su estado
            'filters' => $request->only(['search', 'field']),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo oficio.
     */
    public function create()
    {
        return Inertia::render('Oficios/create', [
            'prioridades' => Prioridad::all(['id', 'nombre']),
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    /**
     * Almacena un nuevo oficio y su archivo adjunto.
     */
    public function store(Request $request)
    {
        // Validación ajustada a los campos del formulario de creación
        $validatedData = $request->validate([
            'folio_oficio' => 'required|string|max:255|unique:oficios',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048', // Validación para el archivo
            'remitente' => 'nullable|string|max:255',
            'asunto' => 'nullable|string|max:255',
            'situacion' => 'nullable|string',
            'folio_interno' => 'nullable|string|max:255|unique:oficios,folio_interno',
            'fecha_recepcion' => 'nullable|date',
            'fecha_limite' => 'nullable|date|after_or_equal:fecha_recepcion',
            'prioridad_id' => 'nullable|exists:prioridades,id',
            'area_id' => 'nullable|exists:areas,id',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Pendiente,En Proceso,Completado',
        ]);

        // Crea el oficio con los datos validados
        $oficio = Oficio::create($validatedData);

        // Manejo del archivo
        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('documentos', 'public');
            $oficio->documento()->create([
                'nombre_documento' => $request->file('archivo')->getClientOriginalName(),
                'ruta_almacenamiento' => $path,
                'tipo_documento' => $request->file('archivo')->getClientOriginalExtension(), // <-- CORRECCIÓN AÑADIDA
            ]);
        }

        return redirect()->route('oficios.index')->with('success', 'Oficio creado correctamente.');
    }

    /**
     * Muestra los detalles de un oficio específico.
     */
    public function show(Oficio $oficio)
    {
        // Carga todas las relaciones necesarias
        $oficio->load(['prioridad', 'area', 'asignadoA', 'documento']);

        return Inertia::render('Oficios/show', [ // Asumo que tienes una vista Show.vue
            'oficio' => $oficio,
        ]);
    }

    /**
     * Muestra el formulario para editar un oficio.
     */
    public function edit(Oficio $oficio)
    {
        $oficio->load('documento'); // Carga el documento actual

        return Inertia::render('Oficios/edit', [
            'oficio' => $oficio,
            // Puedes pasar los selects si son necesarios para la edición
            'prioridades' => Prioridad::all(['id', 'nombre']),
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    /**
     * Actualiza un oficio existente.
     * OJO: Inertia envía archivos con POST, por eso no usamos inyección de tipo Request.
     */
    public function update(Request $request, Oficio $oficio)
    {
         $validatedData = $request->validate([
            // Unificamos los campos de ambos formularios
            'folio_oficio' => ['required', 'string', 'max:255', Rule::unique('oficios')->ignore($oficio->id)],
            'documento' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048', // 'documento' en lugar de 'archivo'
            'asunto' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'fecha_recepcion' => 'nullable|date',
            'remitente' => 'nullable|string|max:255',
            'dependencia_emisora' => 'nullable|string|max:255',
            'dependencia_turno' => 'nullable|string|max:255',
            // Agrega aquí otras reglas de validación si son necesarias
        ]);

        $oficio->update($validatedData);

        // Manejo de la actualización del archivo
        if ($request->hasFile('documento')) {
            // Eliminar el archivo antiguo si existe
            if ($oficio->documento) {
                Storage::disk('public')->delete($oficio->documento->ruta_almacenamiento);
                $oficio->documento->delete();
            }
            // Guardar el nuevo archivo
            $path = $request->file('documento')->store('documentos', 'public');
            $oficio->documento()->create([
                'nombre_documento' => $request->file('documento')->getClientOriginalName(),
                'ruta_almacenamiento' => $path,
                'tipo_documento' => $request->file('documento')->getClientOriginalExtension(), // <-- CORRECCIÓN AÑADIDA
            ]);
        }

        return redirect()->route('oficios.index')->with('success', 'Oficio actualizado correctamente.');
    }

    /**
     * Elimina un oficio y su archivo asociado.
     */
    public function destroy(Oficio $oficio)
    {
        // Eliminar el archivo si existe
        if ($oficio->documento) {
            Storage::disk('public')->delete($oficio->documento->ruta_almacenamiento);
            $oficio->documento->delete();
        }

        $oficio->delete();
        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }
}

<?php

namespace App\Http\Controllers;

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

    // ... index() y otros métodos no cambian ...
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Oficio::query()->with(['expediente', 'documentoPrincipal', 'recibidoPor:id,name']);
        $query->where(function ($q) use ($user) {
            if (in_array($user->role, ['admin', 'director'])) {
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
     * Muestra el formulario para PREPARAR un oficio de SALIDA.
     */
    public function createSalida()
    {
        return Inertia::render('Oficios/CreateSalida', [
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::where('role', 'operativo')->get(['id', 'name']),
            'searchableOficios' => Oficio::latest()->get(['id', 'folio_interno', 'folio_externo', 'folio_salida', 'asunto']),
            'nextFolioSalida' => $this->getNextFolio('salida'),
            'nextFolioInterno' => $this->getNextFolio('interno'),
        ]);
    }

    /**
     * Almacena un oficio de SALIDA.
     */
    public function storeSalida(Request $request)
    {
        // --- CORRECCIÓN: La validación del área ahora es más flexible ---
        $validated = $request->validate([
            'area_ids' => 'required_without:oficio_respuesta_id|nullable|array',
            'area_ids.*' => 'exists:areas,id',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'oficio_respuesta_id' => 'nullable|exists:oficios,id',
            'asignaciones' => 'nullable|array',
            'asignaciones.*.user_id' => 'nullable|exists:users,id',
            'asignaciones.*.permission' => ['required_with:asignaciones.*.user_id', Rule::in(['editor', 'visualizador'])],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $folioSalida = $this->getNextFolio('salida', true);
            $folioInterno = $this->getNextFolio('interno', true);
            
            if (!empty($validated['oficio_respuesta_id'])) {
                $oficioOriginal = Oficio::findOrFail($validated['oficio_respuesta_id']);
                $expediente = $oficioOriginal->expediente;
            } else {
                // Si es un nuevo caso, se necesita al menos un área
                if (empty($validated['area_ids'])) {
                    // Este es un fallback, la validación debería prevenirlo
                    abort(422, 'Se requiere al menos un área para un nuevo expediente.');
                }
                $expediente = Expediente::create([
                    'numero_expediente' => $folioSalida,
                    'titulo' => $validated['asunto'] ?? 'Oficio de Salida sin Asunto',
                ]);
                $expediente->areas()->sync($validated['area_ids']);
            }

            $oficioData = array_merge($validated, [
                'tipo' => 'salida',
                'folio_salida' => $folioSalida,
                'folio_interno' => $folioInterno,
                'recibido_por_user_id' => Auth::id(),
            ]);

            $oficio = $expediente->oficios()->create($oficioData);

            if (!empty($validated['asignaciones'])) {
                foreach ($validated['asignaciones'] as $asignacion) {
                     if($asignacion['user_id']){
                        $oficio->permissions()->create([
                            'user_id' => $asignacion['user_id'],
                            'permission_level' => $asignacion['permission'],
                        ]);
                    }
                }
            }
            
            DB::table('folio_sequences')->where('name', 'salida')->increment('last_number');
            DB::table('folio_sequences')->where('name', 'interno')->increment('last_number');
        });

        return redirect()->route('oficios.createSalida')->with('success', 'Oficio de Salida generado correctamente.');
    }

    /**
     * Muestra el formulario para REGISTRAR un oficio de ENTRADA.
     */
    public function createEntrada()
    {
        return Inertia::render('Oficios/CreateEntrada', [
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::where('role', 'operativo')->get(['id', 'name']),
            'allUsers' => User::all(['id', 'name']),
            'nextFolioInterno' => $this->getNextFolio('interno'),
        ]);
    }
    
    /**
     * Almacena un oficio de ENTRADA (con archivo).
     */
    public function storeEntrada(Request $request)
    {
        $validated = $request->validate([
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
            'folio_externo' => 'required|string|max:255|unique:oficios',
            'remitente' => 'required|string|max:255',
            'asunto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_recepcion' => 'required|date',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'documento_principal' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'recibido_por_user_id' => 'required|exists:users,id',
            'tiene_turno_dgaf' => 'required|boolean',
            'folio_turno_dgaf' => 'required_if:tiene_turno_dgaf,true|nullable|string|max:255',
            'fecha_turno_dgaf' => 'required_if:tiene_turno_dgaf,true|nullable|date',
            'asignaciones' => 'nullable|array',
            'asignaciones.*.user_id' => 'required|exists:users,id',
            'asignaciones.*.permission' => ['required', Rule::in(['editor', 'visualizador'])],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $folioInterno = $this->getNextFolio('interno', true);
            
            $expediente = Expediente::create([
                'numero_expediente' => $validated['folio_externo'],
                'titulo' => $validated['asunto'],
            ]);
            $expediente->areas()->sync($validated['area_ids']);
            
            $oficioData = array_merge($validated, [
                'folio_interno' => $folioInterno,
                'tipo' => 'entrada',
            ]);

            $oficio = $expediente->oficios()->create($oficioData);

            if ($request->hasFile('documento_principal')) {
                $file = $request->file('documento_principal');
                $path = $file->store('documentos', 'public');
                $oficio->documentos()->create([
                    'nombre_documento' => $file->getClientOriginalName(),
                    'ruta_almacenamiento' => $path,
                    'tipo_documento' => $file->getClientOriginalExtension(),
                    'rol_documento' => 'principal',
                ]);
            }
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $anexo) {
                    $pathAnexo = $anexo->store('documentos', 'public');
                    $oficio->documentos()->create([
                        'nombre_documento' => $anexo->getClientOriginalName(),
                        'ruta_almacenamiento' => $pathAnexo,
                        'tipo_documento' => $anexo->getClientOriginalExtension(),
                        'rol_documento' => 'anexo',
                    ]);
                }
            }

            if (!empty($validated['asignaciones'])) {
                foreach ($validated['asignaciones'] as $asignacion) {
                    $oficio->permissions()->updateOrCreate(
                        ['user_id' => $asignacion['user_id']],
                        ['permission_level' => $asignacion['permission']]
                    );
                }
            }
            
            DB::table('folio_sequences')->where('name', 'interno')->increment('last_number');
        });

        // --- CORRECCIÓN: Se asegura la redirección para flashear el mensaje ---
        return to_route('oficios.createEntrada')->with('success', 'Oficio de Entrada registrado correctamente.');
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
        ]);
    }

    /**
     * Actualiza un oficio existente.
     */
    public function update(Request $request, Oficio $oficio)
    {
        $this->authorize('update', $oficio);
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['entrada', 'salida'])],
            'folio_externo' => ['required_if:tipo,entrada', 'nullable', 'string', 'max:255', Rule::unique('oficios')->ignore($oficio->id)],
            'remitente' => 'nullable|string|max:255',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'required|string',
            'descripcion' => 'nullable|string',
            'fecha_recepcion' => 'nullable|date',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'resolucion' => 'nullable|string',
            'oficio_respuesta_id' => 'nullable|exists:oficios,id',
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
            $oficio->delete();
        });
        return redirect()->route('oficios.index')->with('success', 'Oficio eliminado correctamente.');
    }

    /**
     * Helper function to get the next folio number.
     */
    private function getNextFolio(string $name, bool $lockForUpdate = false): string
    {
        $query = DB::table('folio_sequences')->where('name', $name);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $sequence = $query->first();
        
        if (!$sequence) {
            return '0001';
        }
        
        return str_pad($sequence->last_number + 1, 4, '0', STR_PAD_LEFT);
    }
}

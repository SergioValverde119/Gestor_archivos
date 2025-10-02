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
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class OficioEntradaController extends Controller
{
    /**
     * Muestra el formulario para registrar un nuevo oficio de entrada.
     */
    public function create()
    {
        return Inertia::render('Oficios/CreateEntrada', [
            'areas' => Area::all(['id', 'nombre']),
            'users' => User::where('role', 'operativo')->get(['id', 'name']),
            'searchableOficios' => Oficio::latest()->get(['id', 'folio_interno', 'folio_externo', 'folio_salida', 'asunto']),
            'allUsers' => User::all(['id', 'name']),
            'nextFolioInterno' => $this->getNextFolio('interno'),
        ]);
    }
    
    /**
     * Almacena un nuevo oficio de entrada en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
            'folio_externo' => 'required|string|max:255|unique:oficios',
            'remitente' => 'nullable|string|max:255',
            'asunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_recepcion' => 'required|date',
            'fecha_limite' => 'nullable|date',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'documento_principal' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
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
            
            if (!empty($validated['oficio_respuesta_id'])) {
            $oficioOriginal = Oficio::findOrFail($validated['oficio_respuesta_id']);
            $expediente = $oficioOriginal->expediente;

                // --- CORRECCIÓN: Se AÑADEN las nuevas áreas al expediente sin borrar las anteriores ---
             if (!empty($validated['area_ids'])) {
                    $expediente->areas()->syncWithoutDetaching($validated['area_ids']);
                }

            } else {
                 if (empty($validated['area_ids'])) {
                    abort(422, 'Se requiere al menos un área para un nuevo expediente.');
                }
                $expediente = Expediente::create([
                    'numero_expediente' => $folioInterno,
                    'titulo' => $validated['asunto'] ?? 'Oficio de Salida sin Asunto',
                ]);
                $expediente->areas()->sync($validated['area_ids']);
            }

            $expediente = Expediente::create([
                'numero_expediente' => $validated['folio_externo'],
                'titulo' => $validated['asunto'] ?? 'Oficio de Entrada sin Asunto',
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

        return redirect()->route('oficios.createEntrada')->with('success', 'Oficio de Entrada registrado correctamente.');
    }

    /**
     * Helper para obtener el siguiente número de folio.
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
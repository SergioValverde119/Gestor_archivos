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

class OficioController extends Controller
{
    // ... (El método index() y los demás que ya tenías no cambian)

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Oficio::query()->with([
            'expediente', 
            'documentoPrincipal', 
            'recibidoPor:id,name'
        ]);

        // Aplicar filtros de permisos basados en el rol del usuario
        $query->where(function ($q) use ($user) {
            if (in_array($user->role, ['admin', 'director'])) {
                // Sin filtro, acceso total.
            } 
            elseif ($user->role === 'jefe_area' && $user->area_id) {
                $q->whereHas('expediente', function ($expedienteQuery) use ($user) {
                    $expedienteQuery->whereHas('areas', function ($areaQuery) use ($user) {
                        $areaQuery->where('areas.id', $user->area_id);
                    });
                });
            } 
            else {
                 $q->where(function($permissionQuery) use ($user) {
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

        // Aplicar filtros de búsqueda de la interfaz
        $query->when($request->input('search'), function ($q, $search) use ($request) {
            $field = $request->input('field', 'folio_oficio');
            if (in_array($field, ['folio_oficio', 'asunto', 'remitente', 'destinatario', 'status'])) {
                $q->where($field, 'like', "%{$search}%");
            }
        });

        $oficios = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Oficios/index', [
            'oficios' => $oficios,
            'filters' => $request->only(['search', 'field']),
        ]);
    }

    /**
     * Show the form for creating a new resource, generating folios automatically.
     */
    public function create()
    {
        // --- NUEVA LÓGICA DE GENERACIÓN DE FOLIOS ---
        $nextFolioOficio = $this->getNextFolio('oficio');
        $nextFolioInterno = $this->getNextFolio('interno');

        return Inertia::render('Oficios/create', [
            'expedientes' => Expediente::all(['id', 'numero_expediente', 'titulo']),
            // Pasar los nuevos folios a la vista
            'nextFolioOficio' => $nextFolioOficio,
            'nextFolioInterno' => $nextFolioInterno,
        ]);
    }

    /**
     * Store a newly created resource in storage, using the generated folios.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Se quita la validación de los folios, ya que ahora vienen del sistema
            'expediente_id' => 'required|exists:expedientes,id',
            'tipo' => ['required', Rule::in(['entrada', 'salida'])],
            'remitente' => 'nullable|string|max:255',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'required|string',
            'descripcion' => 'nullable|string',
            'fecha_recepcion' => 'nullable|date',
            'prioridad' => ['nullable', Rule::in(['Ordinario', 'Urgente', 'Extremadamente Urgente'])],
            'status' => 'required|string|max:255',
            'documento_principal' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        // --- NUEVA LÓGICA DE REGISTRO DE FOLIOS ---
        $oficio = DB::transaction(function () use ($validated, $request) {
            // 1. Obtener y bloquear los siguientes folios para evitar duplicados
            $folioOficio = $this->getNextFolio('oficio', true);
            $folioInterno = $this->getNextFolio('interno', true);
            
            // 2. Preparar los datos del oficio con los folios generados
            $oficioData = array_merge($validated, [
                'folio_oficio' => $folioOficio,
                'folio_interno' => $folioInterno,
                'expediente_id' => $validated['expediente_id'],
                'recibido_por_user_id' => Auth::id(),
            ]);

            // 3. Crear el oficio
            $oficio = Oficio::create($oficioData);

            // 4. Guardar documentos (principal y anexos)
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
            
            // 5. Incrementar los contadores en la base de datos
            DB::table('folio_sequences')->where('name', 'oficio')->increment('last_number');
            DB::table('folio_sequences')->where('name', 'interno')->increment('last_number');

            return $oficio;
        });

        return redirect()->route('oficios.index')->with('success', 'Oficio creado correctamente.');
    }

    // ... (Los métodos show, edit, update, y destroy no necesitan cambios para esta lógica)

    /**
     * Helper function to get the next folio number.
     *
     * @param string $name 'oficio' or 'interno'
     * @param bool $lockForUpdate
     * @return string
     */
    private function getNextFolio(string $name, bool $lockForUpdate = false): string
    {
        $query = DB::table('folio_sequences')->where('name', $name);

        if ($lockForUpdate) {
            // Bloquea la fila para evitar que otro proceso la lea mientras la usamos
            $query->lockForUpdate();
        }

        $sequence = $query->first();
        
        // Formatea el número con ceros a la izquierda, por ejemplo: 0013
        return str_pad($sequence->last_number + 1, 4, '0', STR_PAD_LEFT);
    }
}
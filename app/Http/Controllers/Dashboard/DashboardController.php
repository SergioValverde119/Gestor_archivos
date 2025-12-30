<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Oficio;
use App\Models\Area;
use Carbon\Carbon; // <--- ¡ESTA FALTABA!

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con estadísticas adaptadas al rol del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if (in_array($user->role, ['admin', 'director'])) {
            // --- Consultas para la Vista Global (Admin/Director) ---

            // A. KPIs Principales
            $data['totalPendientes'] = Oficio::where('status', 'Pendiente')->count();
            
            $data['totalVencidos'] = Oficio::where('status', '!=', 'Resuelto')
                                            ->whereNotNull('fecha_limite')
                                            ->whereDate('fecha_limite', '<', today())
                                            ->count();
            
            // --- NUEVO: KPIs de Estado General ---
            $data['totalUrgentes'] = Oficio::where('status', 'Pendiente')
                                            ->where('prioridad', 'Urgente')
                                            ->count();
            
            $data['nuevosHoy'] = Oficio::whereDate('created_at', today())->count();
            
            
            // B. Para las Gráficas
            $data['entradasVsSalidas'] = Oficio::select('tipo', DB::raw('count(*) as total'))->groupBy('tipo')->get();
            
            // Esta es la gráfica original de "Expedientes"
            $data['expedientesPorArea'] = Area::withCount('expedientes')->orderBy('expedientes_count', 'desc')->get(['id', 'nombre', 'expedientes_count']);

            // --- NUEVO: Análisis de Carga de Trabajo y Vencidos (Minería de Datos) ---
            $baseQuery = DB::table('areas')
                ->join('area_expediente', 'areas.id', '=', 'area_expediente.area_id')
                ->join('expedientes', 'area_expediente.expediente_id', '=', 'expedientes.id')
                ->join('oficios', 'expedientes.id', '=', 'oficios.expediente_id');
            
            // Total PENDIENTES por área (para Carga de Trabajo)
            $data['pendientesPorArea'] = (clone $baseQuery)
                ->where('oficios.status', 'Pendiente')
                ->groupBy('areas.id', 'areas.nombre')
                ->select('areas.nombre', DB::raw('count(oficios.id) as total'))
                ->orderBy('total', 'desc')
                ->get();

            // Total VENCIDOS por área (para Cuellos de Botella)
            $data['vencidosPorArea'] = (clone $baseQuery)
                ->where('oficios.status', '!=', 'Resuelto')
                ->whereNotNull('oficios.fecha_limite')
                ->whereDate('oficios.fecha_limite', '<', today())
                ->groupBy('areas.id', 'areas.nombre')
                ->select('areas.nombre', DB::raw('count(oficios.id) as total'))
                ->orderBy('total', 'desc')
                ->get();


            // C. Para las Listas con Scroll
            $data['ultimasEntradas'] = Oficio::where('tipo', 'entrada')->latest()->take(15)->get(['id', 'asunto', 'folio_externo', 'created_at']);
            $data['ultimasSalidas'] = Oficio::where('tipo', 'salida')->latest()->take(15)->get(['id', 'asunto', 'folio_salida', 'created_at']);

            // D. Para el Mini-Calendario
            $data['fechasPendientes'] = Oficio::where('status', 'Pendiente')->whereNotNull('fecha_limite')->distinct()->pluck('fecha_limite');

        } elseif ($user->role === 'jefe_area' && $user->area_id) {
            
            $areaId = $user->area_id;
            $baseAreaQuery = Oficio::whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId));

            // --- Consultas para la Vista de Jefe de Área ---
            $data['pendientesEnArea'] = (clone $baseAreaQuery)
                                            ->where('status', 'Pendiente')
                                            ->count();
            
            $data['vencidosEnArea'] = (clone $baseAreaQuery)
                                            ->where('status', '!=', 'Resuelto')
                                            ->whereNotNull('fecha_limite')
                                            ->whereDate('fecha_limite', '<', today())
                                            ->count();
                                            
            // --- NUEVO: KPIs de Jefe de Área ---
            $data['urgentesEnArea'] = (clone $baseAreaQuery)
                                            ->where('status', 'Pendiente')
                                            ->where('prioridad', 'Urgente')
                                            ->count();

            $data['nuevosHoyEnArea'] = (clone $baseAreaQuery)
                                            ->whereDate('created_at', today())
                                            ->count();

            // A. Para las Gráficas (filtrado por área)
            $data['entradasVsSalidas'] = (clone $baseAreaQuery)
                                             ->select('tipo', DB::raw('count(*) as total'))
                                             ->groupBy('tipo')->get();

            // B. Para las Listas con Scroll (filtrado por área)
            $data['ultimasEntradas'] = (clone $baseAreaQuery)
                                            ->where('tipo', 'entrada')
                                            ->latest()->take(15)->get(['id', 'asunto', 'folio_externo', 'created_at']);
            
            $data['ultimasSalidas'] = (clone $baseAreaQuery)
                                            ->where('tipo', 'salida')
                                            ->latest()->take(15)->get(['id', 'asunto', 'folio_salida', 'created_at']);

            // C. Para el Mini-Calendario (filtrado por área)
            $data['fechasPendientes'] = (clone $baseAreaQuery)
                                            ->where('status', 'Pendiente')
                                            ->whereNotNull('fecha_limite')
                                            ->distinct()->pluck('fecha_limite');
        }

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Obtiene el detalle de oficios para el modal del dashboard via AJAX
     */
    public function getDetails(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type'); 
        $date = $request->query('date'); // <--- Nuevo parámetro para el calendario
        
        $query = Oficio::query()->with(['recibidoPor:id,name']);

        // --- LÓGICA DE PERMISOS (Igual que antes) ---
        if ($user->role === 'jefe_area' && $user->area_id) {
             $query->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $user->area_id));
        } elseif (!in_array($user->role, ['admin', 'director'])) {
             $query->where(function ($q) use ($user) {
                $q->where('recibido_por_user_id', $user->id)
                  ->orWhereHas('permissions', fn($p) => $p->where('user_id', $user->id));
             });
        }

        // --- FILTROS ---
        switch ($type) {
            case 'pendientes':
                $query->where('status', 'Pendiente');
                break;
            case 'vencidos':
                $query->where('status', '!=', 'Concluido') // Ni Resuelto ni Concluido
                      ->where('fecha_limite', '<', Carbon::now()->startOfDay());
                break;
            case 'urgentes':
                $query->whereIn('prioridad', ['Urgente', 'Extremadamente Urgente'])
                      ->where('status', '!=', 'Concluido');
                break;
            case 'nuevos':
                $query->whereDate('created_at', Carbon::today());
                break;
            // --- NUEVO CASO PARA EL CALENDARIO ---
            case 'calendar':
                if ($date) {
                    // Busca oficios que vencen ESE día específico
                    $query->whereDate('fecha_limite', Carbon::parse($date));
                }
                break;
        }

        // --- SIN LÍMITE (take) PARA QUE COINCIDA CON LOS CONTADORES ---
        $data = $query->latest()
                      ->get(['id', 'asunto', 'folio_interno', 'folio_externo', 'fecha_limite', 'status', 'prioridad', 'created_at']);

        return response()->json($data);
    }
}
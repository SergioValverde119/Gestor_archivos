<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Oficio;
use App\Models\Area;

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
            $data['totalPendientes'] = Oficio::where('status', 'Pendiente')->count();
            $data['totalVencidos'] = Oficio::where('status', '!=', 'Resuelto')
                                        ->whereNotNull('fecha_limite')
                                        ->whereDate('fecha_limite', '<', today())
                                        ->count();
            
            // A. Para las Gráficas
            $data['entradasVsSalidas'] = Oficio::select('tipo', DB::raw('count(*) as total'))->groupBy('tipo')->get();
            $data['oficiosPorArea'] = Area::withCount('expedientes')->orderBy('expedientes_count', 'desc')->get(['id', 'nombre', 'expedientes_count']);

            // B. Para las Listas con Scroll
            $data['ultimasEntradas'] = Oficio::where('tipo', 'entrada')->latest()->take(15)->get(['id', 'asunto', 'folio_externo', 'created_at']);
            $data['ultimasSalidas'] = Oficio::where('tipo', 'salida')->latest()->take(15)->get(['id', 'asunto', 'folio_salida', 'created_at']);

            // C. Para el Mini-Calendario
            $data['fechasPendientes'] = Oficio::where('status', 'Pendiente')->whereNotNull('fecha_limite')->distinct()->pluck('fecha_limite');

        } elseif ($user->role === 'jefe_area' && $user->area_id) {
            
            $areaId = $user->area_id;

            // --- Consultas para la Vista de Jefe de Área ---
            $data['pendientesEnArea'] = Oficio::where('status', 'Pendiente')
                                            ->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                            ->count();
            
            $data['vencidosEnArea'] = Oficio::where('status', '!=', 'Resuelto')
                                            ->whereNotNull('fecha_limite')
                                            ->whereDate('fecha_limite', '<', today())
                                            ->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                            ->count();

            // A. Para las Gráficas (filtrado por área)
            $data['entradasVsSalidas'] = Oficio::whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                             ->select('tipo', DB::raw('count(*) as total'))
                                             ->groupBy('tipo')->get();

            // B. Para las Listas con Scroll (filtrado por área)
            $data['ultimasEntradas'] = Oficio::where('tipo', 'entrada')
                                            ->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                            ->latest()->take(15)->get(['id', 'asunto', 'folio_externo', 'created_at']);
            
            $data['ultimasSalidas'] = Oficio::where('tipo', 'salida')
                                            ->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                            ->latest()->take(15)->get(['id', 'asunto', 'folio_salida', 'created_at']);

            // C. Para el Mini-Calendario (filtrado por área)
            $data['fechasPendientes'] = Oficio::where('status', 'Pendiente')
                                            ->whereNotNull('fecha_limite')
                                            ->whereHas('expediente.areas', fn($q) => $q->where('areas.id', $areaId))
                                            ->distinct()->pluck('fecha_limite');
        }

        return Inertia::render('Dashboard', $data);
    }
}

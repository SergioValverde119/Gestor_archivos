<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Oficio, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// --- Iconos ---
import { AlertTriangle, FileClock, Sparkles, FileWarning } from 'lucide-vue-next';

// --- Gráficas ---
import { Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

// --- Calendario ---
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';

// --- Tipos de Datos ---
interface ChartData {
    tipo: string;
    total: number;
}
interface ExpedientesAreaData {
    nombre: string;
    expedientes_count: number;
}
interface AreaTotalData {
    nombre: string;
    total: number;
}

// --- Props (Datos que vienen del Backend) ---
const props = defineProps<{
    // Admin/Director
    totalPendientes?: number;
    totalVencidos?: number;
    totalUrgentes?: number;
    nuevosHoy?: number;
    entradasVsSalidas?: ChartData[];
    expedientesPorArea?: ExpedientesAreaData[];
    pendientesPorArea?: AreaTotalData[];
    vencidosPorArea?: AreaTotalData[];
    ultimasEntradas?: Oficio[];
    ultimasSalidas?: Oficio[];
    fechasPendientes?: string[];

    // Jefe de Área
    pendientesEnArea?: number;
    vencidosEnArea?: number;
    urgentesEnArea?: number;
    nuevosHoyEnArea?: number;
}>();

const authUser = computed(() => usePage().props.auth.user as User);

// --- Detectar qué vista mostrar ---
const tieneDatosDirectivos = computed(() => props.totalPendientes !== undefined);
const tieneDatosJefeArea = computed(() => props.pendientesEnArea !== undefined);


// ==========================================
// 📅 LÓGICA DEL CALENDARIO (CORREGIDA FINAL)
// ==========================================
const calendarAttributes = computed(() => {
    const dates = props.fechasPendientes || [];
    
    // Definimos "Hoy" limpio (sin horas) para comparar
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    // Función auxiliar para convertir el string SQL a Objeto Fecha JS
    // Corrige el problema de la zona horaria tomando la parte de la fecha solamente
    const parseDate = (dateString: string) => {
        // Cortamos el string para quedarnos solo con "2025-10-15"
        // y le agregamos T12:00:00 para asegurar que caiga en el día correcto
        const cleanDate = dateString.split('T')[0]; 
        return new Date(cleanDate + 'T12:00:00');
    };

    // 1. Filtrar VENCIDOS (Fechas anteriores a hoy)
    const fechasVencidas = dates
        .map(d => parseDate(d)) 
        .filter(d => d < hoy);

    // 2. Filtrar PENDIENTES (Hoy o futuro)
    const fechasPorVencer = dates
        .map(d => parseDate(d))
        .filter(d => d >= hoy);

    return [
        {
            key: 'today',
            highlight: { 
                color: 'blue', 
                fillMode: 'outline' as const, // Solución TypeScript
            },
            dates: [new Date()],
        },
        // Puntos ROJOS: Ya vencieron
        {
            key: 'vencidos',
            dot: 'red',
            dates: fechasVencidas,
            popover: { 
                label: '⚠️ ¡Vencido! Atender de inmediato.', 
                visibility: 'hover' as const 
            },
        },
        // Puntos AMARILLOS: Aún a tiempo
        {
            key: 'proximos',
            dot: 'yellow',
            dates: fechasPorVencer,
            popover: { 
                label: '📅 Pendiente. Estás a tiempo.', 
                visibility: 'hover' as const 
            },
        }
    ];
});
// ==========================================
// 📊 CONFIGURACIÓN DE GRÁFICAS
// ==========================================

// Dona (Entradas vs Salidas)
const doughnutChartData = computed(() => {
    const data = props.entradasVsSalidas || [];
    return {
        labels: data.map(d => d.tipo === 'entrada' ? 'Entrada' : 'Salida'),
        datasets: [{
            backgroundColor: ['#3B82F6', '#10B981'], // Azul / Verde
            data: data.map(d => d.total)
        }]
    }
});

// Barras (Expedientes)
const expedientesBarChartData = computed(() => {
    const data = props.expedientesPorArea || [];
    return {
        labels: data.map(a => a.nombre),
        datasets: [{
            label: 'Total de Expedientes',
            backgroundColor: '#6366F1', // Indigo
            data: data.map(a => a.expedientes_count)
        }]
    }
});

// Barras (Carga de Trabajo)
const pendientesPorAreaChartData = computed(() => {
    const data = props.pendientesPorArea || [];
    return {
        labels: data.map(a => a.nombre),
        datasets: [{
            label: 'Oficios Pendientes',
            backgroundColor: '#F59E0B', // Amarillo
            data: data.map(a => a.total)
        }]
    }
});

// Barras (Cuellos de Botella)
const vencidosPorAreaChartData = computed(() => {
    const data = props.vencidosPorArea || [];
    return {
        labels: data.map(a => a.nombre),
        datasets: [{
            label: 'Oficios Vencidos',
            backgroundColor: '#EF4444', // Rojo
            data: data.map(a => a.total)
        }]
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } }
};

const barChartOptions = {
    ...chartOptions,
    scales: { y: { beginAtZero: true } }
};

// --- Helpers ---
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('es-MX', {
        month: 'short', day: 'numeric'
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <div v-if="tieneDatosDirectivos" class="p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Vista Global (Admin/Director)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <FileClock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalPendientes }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Vencidos</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalVencidos }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                        <FileWarning class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Urgentes</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalUrgentes }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <Sparkles class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Nuevos Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ nuevosHoy }}</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Expedientes por Área</h3>
                    <div class="h-80">
                        <Bar :data="expedientesBarChartData" :options="barChartOptions" />
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fechas Límite</h3>
                    <div class="flex justify-center">
                        <Calendar :attributes="calendarAttributes" is-expanded class="border-0 dark:bg-gray-800" />
                    </div>
                    <div class="mt-4 flex gap-4 text-xs justify-center text-gray-500">
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span> Vencido</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-400 mr-1"></span> Pendiente</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Carga de Trabajo (Pendientes)</h3>
                    <div class="h-80">
                        <Bar :data="pendientesPorAreaChartData" :options="barChartOptions" />
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cuellos de Botella (Vencidos)</h3>
                    <div class="h-80">
                        <Bar :data="vencidosPorAreaChartData" :options="barChartOptions" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Últimas Entradas</h3>
                    <div v-if="ultimasEntradas?.length" class="h-72 overflow-y-auto space-y-2 pr-2">
                        <Link v-for="oficio in ultimasEntradas" :key="oficio.id" :href="`/oficios/${oficio.id}`" 
                              class="block p-3 rounded-md border border-gray-50 hover:bg-gray-50 hover:border-blue-200 transition-colors">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-blue-700 truncate w-3/4">{{ oficio.asunto || 'Sin Asunto' }}</span>
                                <span class="text-xs text-gray-400">{{ formatDate(oficio.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Folio: {{ oficio.folio_externo }}</span>
                                <span :class="`text-[10px] px-2 py-0.5 rounded-full border ${oficio.status === 'Pendiente' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-gray-50 text-gray-600'}`">
                                    {{ oficio.status }}
                                </span>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500 italic">No hay registros recientes.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Últimas Salidas</h3>
                    <div v-if="ultimasSalidas?.length" class="h-72 overflow-y-auto space-y-2 pr-2">
                        <Link v-for="oficio in ultimasSalidas" :key="oficio.id" :href="`/oficios/${oficio.id}`" 
                              class="block p-3 rounded-md border border-gray-50 hover:bg-gray-50 hover:border-green-200 transition-colors">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-green-700 truncate w-3/4">{{ oficio.asunto || 'Sin Asunto' }}</span>
                                <span class="text-xs text-gray-400">{{ formatDate(oficio.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Folio: {{ oficio.folio_salida }}</span>
                                <span :class="`text-[10px] px-2 py-0.5 rounded-full border ${oficio.status === 'Pendiente' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-gray-50 text-gray-600'}`">
                                    {{ oficio.status }}
                                </span>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500 italic">No hay registros recientes.</p>
                </div>
            </div>
        </div>

        <div v-else-if="tieneDatosJefeArea" class="p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-900">Dashboard de Área: {{ authUser.area?.nombre }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <FileClock class="w-6 h-6 text-yellow-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900">{{ pendientesEnArea }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-red-100">
                        <AlertTriangle class="w-6 h-6 text-red-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Vencidos</p>
                        <p class="text-3xl font-bold text-gray-900">{{ vencidosEnArea }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-orange-100">
                        <FileWarning class="w-6 h-6 text-orange-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Urgentes</p>
                        <p class="text-3xl font-bold text-gray-900">{{ urgentesEnArea }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-blue-100">
                        <Sparkles class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Nuevos Hoy</p>
                        <p class="text-3xl font-bold text-gray-900">{{ nuevosHoyEnArea }}</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Entradas vs. Salidas (Mi Área)</h3>
                    <div class="h-80">
                        <Doughnut :data="doughnutChartData" :options="chartOptions" />
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Fechas Límite (Mi Área)</h3>
                    <div class="flex justify-center">
                        <Calendar :attributes="calendarAttributes" is-expanded class="border-0" />
                    </div>
                    <div class="mt-4 flex gap-4 text-xs justify-center text-gray-500">
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span> Vencido</span>
                        <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-400 mr-1"></span> Pendiente</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="p-6 text-center py-20">
            <div class="animate-pulse flex flex-col items-center">
                <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
                <div class="h-32 bg-gray-200 rounded w-full max-w-md"></div>
            </div>
            <p class="mt-4 text-gray-500">Cargando estadísticas...</p>
        </div>
        
    </AppLayout>
</template>
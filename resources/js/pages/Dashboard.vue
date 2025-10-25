<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Oficio, type User, type Area } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { AlertTriangle, FileClock, Inbox, TrendingUp, Users, ChevronsRight, File as FileIcon } from 'lucide-vue-next';

// --- Importaciones para Gráficas ---
import { Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

// --- Importaciones para Calendario ---
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';


// --- Definición de Tipos ---
interface ChartData {
    tipo: string;
    total: number;
}
interface AreaData {
    nombre: string;
    expedientes_count: number;
}

const props = defineProps<{
    // Props para Admin/Director
    totalPendientes?: number;
    totalVencidos?: number;
    entradasVsSalidas?: ChartData[];
    oficiosPorArea?: AreaData[];
    ultimasEntradas?: Oficio[];
    ultimasSalidas?: Oficio[];
    fechasPendientes?: string[];

    // Props para Jefe de Área
    pendientesEnArea?: number;
    vencidosEnArea?: number;
    ultimosOficiosArea?: Oficio[];
}>();

const authUser = computed(() => usePage().props.auth.user as User);

// --- Lógica de Roles y Redirección ---
const tieneDatosDirectivos = computed(() => props.totalPendientes !== undefined);
const tieneDatosJefeArea = computed(() => props.pendientesEnArea !== undefined);

onMounted(() => {
    // Si el usuario es 'operativo', no recibe props y lo redirigimos
    if (authUser.value.role === 'operativo' && !tieneDatosDirectivos.value && !tieneDatosJefeArea.value) {
        router.replace('/oficios'); // Lo mandamos a su lista de tareas
    }
});

// --- Lógica para Gráfica de Dona (Entrada vs Salida) ---
const doughnutChartData = computed(() => {
    const data = props.entradasVsSalidas || [];
    return {
        labels: data.map(d => d.tipo === 'entrada' ? 'Entrada' : 'Salida'),
        datasets: [{
            backgroundColor: ['#3B82F6', '#10B981'], // Azul y Verde
            data: data.map(d => d.total)
        }]
    }
});

// --- Lógica para Gráfica de Barras (Oficios por Área) ---
const barChartData = computed(() => {
    const data = props.oficiosPorArea || [];
    return {
        labels: data.map(a => a.nombre),
        datasets: [{
            label: 'Total de Expedientes',
            backgroundColor: '#6366F1', // Indigo
            data: data.map(a => a.expedientes_count)
        }]
    }
});

// --- Lógica para el Calendario ---
const calendarAttributes = computed(() => {
    const dates = props.fechasPendientes || [];
    return [
        {
            key: 'today',
            highlight: {
                color: 'blue',
                fillMode: 'outline' as const, // CORRECCIÓN
            },
            dates: [new Date()], // CORRECCIÓN: Debe ser un array
        },
        {
            key: 'pendientes',
            dot: 'red',
            dates: dates.map(d => new Date(d + 'T12:00:00')), // Se ajusta la zona horaria
            popover: {
                label: 'Tiene oficios pendientes con esta fecha límite.',
            },
        }
    ];
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const
        }
    }
};

const barChartOptions = {
    ...chartOptions,
    scales: {
        y: {
            beginAtZero: true
        }
    }
};


// --- Funciones de Formateo ---
const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-MX', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <!-- CORRECCIÓN: Se añade la prop :breadcrumbs -->
    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Vista para Admin / Director -->
        <div v-if="tieneDatosDirectivos" class="p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Vista Global (Admin/Director)</h2>
            
            <!-- Tarjetas de Estadísticas (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <FileClock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalPendientes }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Vencidos</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalVencidos }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <Inbox class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Expedientes</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ oficiosPorArea?.reduce((acc, area) => acc + area.expedientes_count, 0) || 0 }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Sección de Gráficas y Calendario -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Expedientes por Área</h3>
                    <div class="h-80">
                        <Bar :data="barChartData" :options="barChartOptions" />
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fechas Límite Pendientes</h3>
                    <Calendar :attributes="calendarAttributes" is-expanded class="border-0 dark:bg-gray-800" />
                </div>
            </div>

            <!-- Listas de Últimos Oficios (con scroll) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Últimas Entradas</h3>
                    <!-- CORRECCIÓN: Se cambia 'divV-if' por 'v-if' -->
                    <div v-if="ultimasEntradas && ultimasEntradas.length > 0" class="h-72 overflow-y-auto space-y-3">
                        <Link v-for="oficio in ultimasEntradas" :key="oficio.id" :href="`/oficios/${oficio.id}`" class="block p-3 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400 truncate">{{ oficio.asunto }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">{{ formatDate(oficio.created_at) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Folio: {{ oficio.folio_externo }}</p>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay oficios de entrada recientes.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Últimas Salidas</h3>
                    <!-- CORRECCIÓN: Se cambia 'divV-if' por 'v-if' -->
                    <div v-if="ultimasSalidas && ultimasSalidas.length > 0" class="h-72 overflow-y-auto space-y-3">
                        <Link v-for="oficio in ultimasSalidas" :key="oficio.id" :href="`/oficios/${oficio.id}`" class="block p-3 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-600 dark:text-green-400 truncate">{{ oficio.asunto }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">{{ formatDate(oficio.created_at) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Folio: {{ oficio.folio_salida }}</p>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay oficios de salida recientes.</p>
                </div>
            </div>

        </div>

        <!-- Vista para Jefe de Área -->
        <div v-else-if="tieneDatosJefeArea" class="p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Dashboard de Área: {{ authUser.area?.nombre }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <FileClock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes en mi Área</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ pendientesEnArea }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vencidos en mi Área</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ vencidosEnArea }}</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Entradas vs. Salidas (Mi Área)</h3>
                    <div class="h-80">
                        <Doughnut :data="doughnutChartData" :options="chartOptions" />
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Fechas Límite (Mi Área)</h3>
                    <Calendar :attributes="calendarAttributes" is-expanded class="border-0 dark:bg-gray-800" />
                </div>
            </div>
        </div>

        <!-- Vista para Operativo (o mientras cargan los datos) -->
        <div v-else class="p-6">
            <p class="text-gray-700 dark:text-gray-300">Cargando dashboard...</p>
        </div>
        
    </AppLayout>
</template>


<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import axios from 'axios'; 

// --- WAYFINDER IMPORTS ---
import * as oficioRoutes from '@/routes/oficios';
import * as dashboardRoutes from '@/routes/dashboard';

// --- FULLCALENDAR IMPORTS ---
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es'; // Español nativo

// --- ICONOS ---
import { 
    AlertTriangle, FileClock, Sparkles, FileWarning, 
    ArrowUpRight, ArrowDownLeft, FileText, X, Loader2, Eye, 
    Calendar as CalendarIcon, Clock
} from 'lucide-vue-next';

// --- GRÁFICAS ---
import { Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

// --- PROPS ---
const props = defineProps<{
    totalPendientes?: number;
    totalVencidos?: number;
    totalUrgentes?: number;
    nuevosHoy?: number;
    entradasVsSalidas?: any[];
    pendientesPorArea?: any[];
    vencidosPorArea?: any[];
    ultimasEntradas?: any[];
    ultimasSalidas?: any[];
    fechasPendientes?: string[]; // Array de strings ['2023-10-01', '2023-10-05']
    pendientesEnArea?: number;
    vencidosEnArea?: number;
    urgentesEnArea?: number;
    nuevosHoyEnArea?: number;
}>();

const authUser = computed(() => usePage().props.auth.user as User);
const tieneDatosDirectivos = computed(() => props.totalPendientes !== undefined);
const tieneDatosJefeArea = computed(() => props.pendientesEnArea !== undefined);

// ==========================================
// 📅 CONFIGURACIÓN FULLCALENDAR
// ==========================================

// Transformamos las fechas simples en "Eventos" para el calendario
const calendarEvents = computed(() => {
    if (!props.fechasPendientes) return [];
    
    // Agrupamos fechas duplicadas para saber cuántos hay por día (opcional, visual)
    // O simplemente mapeamos cada fecha a un evento genérico
    return props.fechasPendientes.map(dateStr => {
        // Obtenemos fecha limpia
        const date = dateStr.split('T')[0];
        
        // Decidimos color si ya venció
        const isOverdue = new Date(date) < new Date(new Date().setHours(0,0,0,0));
        
        return {
            title: isOverdue ? '⚠️ Vencimiento' : '📅 Pendiente',
            start: date,
            allDay: true,
            color: isOverdue ? '#EF4444' : '#EAB308', // Rojo o Amarillo
            textColor: isOverdue ? '#fff' : '#000',
            classNames: ['cursor-pointer', 'font-bold', 'text-xs']
        };
    });
});

const calendarOptions = computed(() => ({
    plugins: [ dayGridPlugin, interactionPlugin ],
    initialView: 'dayGridMonth',
    locale: esLocale, // Español automático
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek' // Opcional: dayGridDay
    },
    buttonText: {
        today: 'Hoy',
        month: 'Mes',
        week: 'Semana',
        day: 'Día'
    },
    height: 'auto', // Se adapta a la altura del contenedor
    contentHeight: 500, // Altura mínima cómoda
    events: calendarEvents.value,
    dayMaxEvents: true, // Si hay muchos, pone "+2 more"
    
    // --- EVENTOS CLICK ---
    dateClick: (info: any) => {
        // Clic en la celda vacía
        openModal('calendar', `Agenda del ${formatDate(info.dateStr)}`, info.dateStr);
    },
    eventClick: (info: any) => {
        // Clic en la barrita de color
        const dateStr = info.event.startStr;
        openModal('calendar', `Agenda del ${formatDate(dateStr)}`, dateStr);
    }
}));

// ==========================================
// 🛠 LÓGICA DEL MODAL
// ==========================================
const isModalOpen = ref(false);
const modalTitle = ref('');
const modalData = ref<any[]>([]);
const isLoadingDetails = ref(false);

const openModal = async (type: 'pendientes' | 'vencidos' | 'urgentes' | 'nuevos' | 'calendar', title: string, dateString?: string) => {
    isModalOpen.value = true;
    modalTitle.value = title;
    modalData.value = [];
    isLoadingDetails.value = true;

    try {
        const url = dashboardRoutes.details.url({ 
            query: { type: type, date: dateString } 
        });
        const response = await axios.get(url);
        modalData.value = response.data;
    } catch (error) {
        console.error("Error cargando detalles", error);
    } finally {
        isLoadingDetails.value = false;
    }
};

const closeModal = () => { isModalOpen.value = false; };

// ==========================================
// 📊 GRÁFICAS Y UTILIDADES
// ==========================================
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('es-MX', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getDaysOverdue = (dateString: string) => {
    if (!dateString) return 0;
    const deadline = new Date(dateString);
    const today = new Date();
    deadline.setHours(0,0,0,0);
    today.setHours(0,0,0,0);
    const diffTime = today.getTime() - deadline.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
};

// Configuración ChartJS
const doughnutChartData = computed(() => ({
    labels: (props.entradasVsSalidas || []).map(d => d.tipo === 'entrada' ? 'Entrada' : 'Salida'),
    datasets: [{ backgroundColor: ['#3B82F6', '#10B981'], hoverOffset: 4, data: (props.entradasVsSalidas || []).map(d => d.total) }]
}));
const pendientesPorAreaChartData = computed(() => ({
    labels: (props.pendientesPorArea || []).map(a => a.nombre),
    datasets: [{ label: 'Pendientes', backgroundColor: '#F59E0B', borderRadius: 4, data: (props.pendientesPorArea || []).map(a => a.total) }]
}));
const vencidosPorAreaChartData = computed(() => ({
    labels: (props.vencidosPorArea || []).map(a => a.nombre),
    datasets: [{ label: 'Vencidos', backgroundColor: '#EF4444', borderRadius: 4, data: (props.vencidosPorArea || []).map(a => a.total) }]
}));
const commonOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' as const } } };
const barChartOptions = { ...commonOptions, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } } } as any;
const doughnutOptions = { ...commonOptions } as any;

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Panel de Control' }];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-50/50 dark:bg-gray-900 p-6 md:p-8 space-y-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Hola, {{ authUser.name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Resumen de actividad.</p>
                </div>
            </div>

            <div v-if="tieneDatosDirectivos" class="space-y-8 animate-fade-in-down">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div @click="openModal('pendientes', 'Oficios Pendientes')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10">
                            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pendientes</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ totalPendientes }}</h3></div>
                            <div class="p-3 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl text-white shadow-lg"><FileClock class="w-6 h-6" /></div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-yellow-400 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                    
                    <div @click="openModal('vencidos', 'Oficios Vencidos')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10">
                            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Vencidos</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ totalVencidos }}</h3></div>
                            <div class="p-3 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl text-white shadow-lg"><AlertTriangle class="w-6 h-6" /></div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-red-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>

                    <div @click="openModal('urgentes', 'Trámites Urgentes')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10">
                            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Prioridad Alta</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ totalUrgentes }}</h3></div>
                            <div class="p-3 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl text-white shadow-lg"><FileWarning class="w-6 h-6" /></div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-orange-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>

                    <div @click="openModal('nuevos', 'Registrados Hoy')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10">
                            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nuevos Hoy</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ nuevosHoy }}</h3></div>
                            <div class="p-3 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl text-white shadow-lg"><Sparkles class="w-6 h-6" /></div>
                        </div>
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                </div>
                
                <div class="w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <CalendarIcon class="w-5 h-5 text-blue-500" />
                                Agenda de Vencimientos
                            </h3>
                        </div>
                        
                        <div class="calendar-wrapper">
                            <FullCalendar :options="calendarOptions" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Carga de Trabajo</h3>
                        <div class="h-64"><Bar :data="pendientesPorAreaChartData" :options="barChartOptions" /></div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Críticos</h3>
                        <div class="h-64"><Bar :data="vencidosPorAreaChartData" :options="barChartOptions" /></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-[500px]">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800">
                            <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <span class="p-1.5 bg-blue-100 text-blue-600 rounded-lg"><ArrowDownLeft class="w-4 h-4"/></span> Últimas Entradas
                            </h3>
                            <Link :href="oficioRoutes.index.url({ query: { tipo: 'entrada' } })" class="text-xs font-medium text-blue-600 hover:text-blue-800">Ver todas</Link>
                        </div>
                        <div class="p-4 flex-grow overflow-y-auto custom-scrollbar space-y-3">
                            <Link v-for="oficio in ultimasEntradas" :key="oficio.id" 
                                  :href="oficioRoutes.show.url(oficio.id)" 
                                  class="group flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all border border-transparent hover:border-gray-200">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="min-w-[40px] h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shadow-sm">ENT</div>
                                    <div class="truncate">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate group-hover:text-blue-600 transition-colors">{{ oficio.asunto || 'Sin Asunto' }}</p>
                                        <p class="text-xs text-gray-500">Folio: {{ oficio.folio_externo }}</p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 flex-shrink-0">{{ formatDate(oficio.created_at) }}</p>
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-[500px]">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800">
                            <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <span class="p-1.5 bg-green-100 text-green-600 rounded-lg"><ArrowUpRight class="w-4 h-4"/></span> Últimas Salidas
                            </h3>
                            <Link :href="oficioRoutes.index.url({ query: { tipo: 'salida' } })" class="text-xs font-medium text-green-600 hover:text-green-800">Ver todas</Link>
                        </div>
                        <div class="p-4 flex-grow overflow-y-auto custom-scrollbar space-y-3">
                            <Link v-for="oficio in ultimasSalidas" :key="oficio.id" 
                                  :href="oficioRoutes.show.url(oficio.id)" 
                                  class="group flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all border border-transparent hover:border-gray-200">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="min-w-[40px] h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xs font-bold shadow-sm">SAL</div>
                                    <div class="truncate">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate group-hover:text-green-600 transition-colors">{{ oficio.asunto || 'Sin Asunto' }}</p>
                                        <p class="text-xs text-gray-500">Folio: {{ oficio.folio_salida }}</p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 flex-shrink-0">{{ formatDate(oficio.created_at) }}</p>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="tieneDatosJefeArea" class="space-y-8 animate-fade-in-down">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div @click="openModal('pendientes', 'Mis Pendientes')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10"><div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mis Pendientes</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ pendientesEnArea }}</h3></div><div class="p-3 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl text-white shadow-lg"><FileClock class="w-6 h-6" /></div></div><div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                    <div @click="openModal('vencidos', 'Vencidos en Área')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10"><div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Vencidos</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ vencidosEnArea }}</h3></div><div class="p-3 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl text-white shadow-lg"><AlertTriangle class="w-6 h-6" /></div></div><div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                    <div @click="openModal('urgentes', 'Urgentes en Área')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10"><div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Urgentes</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ urgentesEnArea }}</h3></div><div class="p-3 bg-gradient-to-br from-orange-500 to-deep-orange-600 rounded-xl text-white shadow-lg"><FileWarning class="w-6 h-6" /></div></div><div class="absolute bottom-0 left-0 w-full h-1 bg-orange-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                    <div @click="openModal('nuevos', 'Nuevos Hoy')" class="cursor-pointer bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 group relative overflow-hidden">
                        <div class="flex justify-between items-start relative z-10"><div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nuevos Hoy</p><h3 class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ nuevosHoyEnArea }}</h3></div><div class="p-3 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl text-white shadow-lg"><Sparkles class="w-6 h-6" /></div></div><div class="absolute bottom-0 left-0 w-full h-1 bg-indigo-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    </div>
                </div>

                <div class="w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col h-full">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Agenda de Trabajo</h3>
                        <div class="calendar-wrapper">
                            <FullCalendar :options="calendarOptions" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center h-96 opacity-50">
                <Loader2 class="w-12 h-12 text-blue-500 animate-spin mb-4" />
                <p class="text-gray-500 text-sm animate-pulse">Cargando tablero...</p>
            </div>
            
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden animate-scale-up border border-gray-100 dark:border-gray-700">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span> 
                        {{ modalTitle }} 
                        <span class="text-sm font-normal text-gray-500 ml-2" v-if="modalData.length">({{ modalData.length }})</span>
                    </h3>
                    <button @click="closeModal" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors"><X class="w-5 h-5 text-gray-500" /></button>
                </div>

                <div class="flex-grow overflow-y-auto bg-white dark:bg-gray-800">
                    <div v-if="isLoadingDetails" class="flex flex-col items-center justify-center h-48 space-y-3"><Loader2 class="w-8 h-8 text-blue-500 animate-spin" /><p class="text-sm text-gray-500">Cargando...</p></div>
                    <div v-else-if="modalData.length === 0" class="flex flex-col items-center justify-center h-48 space-y-2 text-gray-400"><FileText class="w-12 h-12 opacity-30" /><p>Sin registros.</p></div>
                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="item in modalData" :key="item.id" class="p-4 hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition-colors group flex items-start gap-4">
                            <div class="mt-1.5">
                                <div v-if="item.status === 'Pendiente'" class="w-2.5 h-2.5 rounded-full bg-yellow-400 ring-4 ring-yellow-100 dark:ring-yellow-900/30"></div>
                                <div v-else-if="item.status === 'Concluido'" class="w-2.5 h-2.5 rounded-full bg-green-500 ring-4 ring-green-100 dark:ring-green-900/30"></div>
                                <div v-else class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="text-sm font-bold text-gray-800 dark:text-white truncate pr-2">{{ item.asunto || 'Sin Asunto' }}</h4>
                                    <span class="text-xs font-mono text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600">{{ item.folio_interno }}</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 truncate">{{ item.folio_externo ? `Ext: ${item.folio_externo}` : 'Sin folio externo' }}</p>
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span v-if="item.prioridad === 'Urgente'" class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full border border-red-200">URGENTE</span>
                                    <span v-if="item.fecha_limite" class="text-[10px] px-2 py-0.5 rounded-full border flex items-center gap-1" :class="getDaysOverdue(item.fecha_limite) > 0 ? 'bg-red-50 text-red-700 border-red-100' : 'bg-blue-50 text-blue-700 border-blue-100'">
                                        <Clock class="w-3 h-3"/> {{ getDaysOverdue(item.fecha_limite) > 0 ? `Venció hace ${getDaysOverdue(item.fecha_limite)} días` : `Vence el: ${formatDate(item.fecha_limite)}` }}
                                    </span>
                                </div>
                            </div>
                            <Link :href="oficioRoutes.show.url(item.id)" class="self-center p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-100 rounded-full transition-all opacity-0 group-hover:opacity-100"><Eye class="w-5 h-5" /></Link>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 p-3 border-t border-gray-100 dark:border-gray-700 text-center"><button @click="closeModal" class="text-xs text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-medium uppercase tracking-wide">Cerrar</button></div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-down { animation: fadeInDown 0.6s ease-out forwards; }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-15px); } to { opacity: 1; transform: translateY(0); } }
.animate-scale-up { animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes scaleUp { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

/* Estilos de Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #475569; }

/* ESTILOS PERSONALIZADOS PARA FULLCALENDAR */
:deep(.fc) {
    font-family: inherit; /* Heredar fuente del sistema */
    --fc-border-color: #e5e7eb; /* Color de bordes gris claro */
    --fc-button-text-color: #fff;
    --fc-button-bg-color: #3b82f6;
    --fc-button-border-color: #3b82f6;
    --fc-button-hover-bg-color: #2563eb;
    --fc-button-hover-border-color: #2563eb;
    --fc-button-active-bg-color: #1d4ed8;
    --fc-button-active-border-color: #1d4ed8;
    --fc-today-bg-color: #eff6ff; /* Fondo azul muy claro para hoy */
}

:deep(.dark .fc) {
    --fc-border-color: #374151;
    --fc-page-bg-color: #1f2937;
    --fc-neutral-bg-color: #374151;
    --fc-list-event-hover-bg-color: #4b5563;
    --fc-today-bg-color: #1e3a8a30;
}

:deep(.fc-col-header-cell) {
    background-color: #f9fafb;
    padding: 10px 0;
    text-transform: uppercase;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
}
:deep(.dark .fc-col-header-cell) {
    background-color: #374151;
    color: #d1d5db;
}

:deep(.fc-daygrid-day-number) {
    color: #374151;
    font-weight: 500;
    padding: 8px;
}
:deep(.dark .fc-daygrid-day-number) {
    color: #e5e7eb;
}

/* Eventos estilizados */
:deep(.fc-event) {
    border: none;
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
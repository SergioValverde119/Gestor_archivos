<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed, onMounted } from 'vue';
import { debounce } from 'lodash';
import OficioTable from './Partials/OficioTable.vue';
// Agregamos los iconos de Folder
import { Search, ChevronDown, RefreshCw, FolderInput, FolderOutput } from 'lucide-vue-next';

import { 
    type BreadcrumbItem,
    type PaginatedOficios, 
    type Area, 
    type OficioFilters 
} from '@/types';

const props = defineProps<{
  oficios: PaginatedOficios;
  areas: Area[]; 
  filters: OficioFilters;
}>();

// --- Estado VISUAL para las pestañas (No afecta lógica real aún) ---
const currentTab = ref('entrada'); 

const filters = ref<OficioFilters>({
    search: props.filters.search || '',
    sort: props.filters.sort,
    direction: props.filters.direction,
    tiene_turno_dgaf: props.filters.tiene_turno_dgaf ?? null,
    tipo: props.filters.tipo ?? null,
    per_page: props.filters.per_page || 8,
    date_from: props.filters.date_from,
    date_to: props.filters.date_to,
    recepcion_from: props.filters.recepcion_from,
    recepcion_to: props.filters.recepcion_to,
    limite_from: props.filters.limite_from,
    limite_to: props.filters.limite_to,
    area_ids: props.filters.area_ids || [],
});

const allColumns = ref([
    { key: 'acciones', label: 'Acciones' },
    { key: 'tipo', label: 'Tipo' },
    { key: 'folio', label: 'Folio' },
    { key: 'asunto', label: 'Asunto' },
    { key: 'turnoDGAF', label: 'Turno DGAF' },
    { key: 'status', label: 'Estado' },
    { key: 'prioridad', label: 'Prioridad' },
    { key: 'fechaRegistro', label: 'Fecha Reg.' },
    { key: 'folioInterno', label: 'Folio Interno' },
    { key: 'expediente', label: 'Expediente' },
    { key: 'remitente_destinatario', label: 'Remitente / Dest.' },
    { key: 'descripcion', label: 'Descripción' },
    { key: 'fechaRecepcion', label: 'Fecha Recep.' },
    { key: 'fechaLimite', label: 'Fecha Límite' },
    { key: 'registrado_por', label: 'Registrado Por' },
    { key: 'areas', label: 'Áreas' },
    { key: 'asignadoA', label: 'Asignado A' },
    { key: 'responde_a', label: 'Responde A' },
    { key: 'anexos', label: 'Anexos' },
]);
const visibleColumns = ref<string[]>([]);

onMounted(() => {
    const saved = localStorage.getItem('visibleOficioColumns');
    if (saved) {
        visibleColumns.value = JSON.parse(saved);
    } else {
        visibleColumns.value = ['acciones', 'tipo', 'folio', 'asunto', 'turnoDGAF', 'status', 'prioridad', 'fechaRegistro'];
    }
});

watch(visibleColumns, (newValue) => {
    localStorage.setItem('visibleOficioColumns', JSON.stringify(newValue));
}, { deep: true });

const visibleHeaders = computed(() => {
    const actionsColumn = allColumns.value.find(c => c.key === 'acciones');
    const otherHeaders = allColumns.value.filter(c => 
        visibleColumns.value.includes(c.key) && c.key !== 'acciones'
    );

    if (actionsColumn && visibleColumns.value.includes('acciones')) {
        return [actionsColumn, ...otherHeaders];
    }
    
    return otherHeaders;
});

watch(filters, debounce(() => {
    const queryParams: any = {};
    for (const key in filters.value) {
        const value = filters.value[key as keyof typeof filters.value];
        if (value !== null && (Array.isArray(value) ? value.length > 0 : value !== '')) {
             queryParams[key] = value;
        } else if (key === 'search' && value === '') {
             queryParams[key] = '';
        }
    }
    router.get('/oficios', queryParams, { preserveState: true, replace: true });
}, 300), { deep: true });

const sortBy = (payload: { column: string, direction: 'asc' | 'desc' }) => {
    filters.value.sort = payload.column;
    filters.value.direction = payload.direction;
};

const resetFilters = () => {
    filters.value = {
        search: '',
        sort: null,
        direction: null,
        tiene_turno_dgaf: null,
        tipo: null,
        per_page: 8,
        date_from: null,
        date_to: null,
        recepcion_from: null,
        recepcion_to: null,
        limite_from: null,
        limite_to: null,
        area_ids: [],
    };
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Buscar Oficios', href: '/oficios' },
];
</script>

<template>
  <Head title="Buscar Oficios" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
      
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Gestión de Oficios</h1>

      <div class="flex items-end space-x-2 pl-2">
        
        <button 
            @click="currentTab = 'entrada'"
            class="group relative px-6 py-3 text-sm font-medium rounded-t-xl border-t border-l border-r transition-all duration-200 ease-in-out flex items-center gap-2"
            :class="[
                currentTab === 'entrada' 
                    ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 border-gray-300 dark:border-gray-700 z-10 -mb-px pb-4 shadow-[0_-2px_5px_rgba(0,0,0,0.05)]' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 border-transparent hover:border-gray-300 mb-0'
            ]"
        >
            <FolderInput class="w-4 h-4" :class="{ 'text-blue-500': currentTab === 'entrada' }" />
            Entrada
            
            <div v-if="currentTab === 'entrada'" class="absolute top-0 left-0 w-full h-1 bg-blue-500 rounded-t-xl"></div>
        </button>

        <button 
            @click="currentTab = 'salida'"
            class="group relative px-6 py-3 text-sm font-medium rounded-t-xl border-t border-l border-r transition-all duration-200 ease-in-out flex items-center gap-2"
            :class="[
                currentTab === 'salida' 
                    ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 border-gray-300 dark:border-gray-700 z-10 -mb-px pb-4 shadow-[0_-2px_5px_rgba(0,0,0,0.05)]' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 border-transparent hover:border-gray-300 mb-0'
            ]"
        >
            <FolderOutput class="w-4 h-4" :class="{ 'text-purple-500': currentTab === 'salida' }" />
            Salida

            <div v-if="currentTab === 'salida'" class="absolute top-0 left-0 w-full h-1 bg-purple-500 rounded-t-xl"></div>
        </button>
      </div>
      
      <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-b-lg rounded-tr-lg shadow-sm p-6 relative z-0">
          
          <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
            <div class="relative w-full md:w-1/2">
              <input type="text" v-model="filters.search" placeholder="Buscar..." class="w-full rounded-md shadow-sm pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <Search class="h-5 w-5 text-gray-400" />
              </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <details class="group">
                        <summary class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md cursor-pointer">
                            Columnas <ChevronDown class="w-4 h-4 group-open:rotate-180 transition-transform"/>
                        </summary>
                        <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg z-50">
                            <div class="p-2 grid grid-cols-2 gap-2">
                                <label v-for="col in allColumns" :key="col.key" class="flex items-center space-x-2 text-sm">
                                    <input type="checkbox" :value="col.key" v-model="visibleColumns" class="rounded"/>
                                    <span>{{ col.label }}</span>
                                </label>
                            </div>
                        </div>
                    </details>
                </div>
                <button @click="resetFilters" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700" title="Resetear filtros y ordenamiento">
                    <RefreshCw class="w-5 h-5" />
                </button>
            </div>
          </div>

          <OficioTable 
            :oficios="props.oficios" 
            v-model:filters="filters"
            :visible-headers="visibleHeaders"
            :areas="props.areas"
            @sort="sortBy"
          />
      </div>
      </div>
  </AppLayout>
</template>
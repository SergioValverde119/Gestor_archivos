<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed, onMounted } from 'vue';
import { debounce } from 'lodash';
import OficioTable from './Partials/OficioTable.vue';
import { Search, ChevronDown, RefreshCw } from 'lucide-vue-next';

// --- CORRECCIÓN: Se importan los tipos desde el archivo central ---
import { 
    type BreadcrumbItem,
    type PaginatedOficios, 
    type Area, 
    type OficioFilters 
} from '@/types';

// --- (Las definiciones locales de tipos se han eliminado) ---

const props = defineProps<{
  oficios: PaginatedOficios;
  areas: Area[]; 
  filters: OficioFilters;
}>();

// --- Lógica de Búsqueda y Filtros ---
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

// --- Lógica para Columnas Personalizables ---
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
        // CORRECCIÓN: Se asegura de que los arrays vacíos no se envíen, pero el string de búsqueda vacío sí.
        if (value !== null && (Array.isArray(value) ? value.length > 0 : value !== '')) {
             queryParams[key] = value;
        } else if (key === 'search' && value === '') {
             queryParams[key] = '';
        }
    }
    router.get('/oficios', queryParams, { preserveState: true, replace: true });
}, 300), { deep: true });

// --- Lógica para Ordenar Columnas ---
const sortBy = (payload: { column: string, direction: 'asc' | 'desc' }) => {
    filters.value.sort = payload.column;
    filters.value.direction = payload.direction;
};

// --- Función para Resetear todos los filtros ---
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
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Búsqueda de Oficios</h1>
      
      <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <div class="relative w-full md:w-1/2">
          <input type="text" v-model="filters.search" placeholder="Buscar..." class="w-full rounded-md shadow-sm pl-10" />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="h-5 w-5 text-gray-400" />
          </div>
        </div>
        <div class="flex items-center space-x-2">
            <div class="relative">
                <details class="group">
                    <summary class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-md cursor-pointer">
                        Columnas <ChevronDown class="w-4 h-4 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg z-10">
                        <div class="p-2 grid grid-cols-2 gap-2">
                            <label v-for="col in allColumns" :key="col.key" class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" :value="col.key" v-model="visibleColumns" class="rounded"/>
                                <span>{{ col.label }}</span>
                            </label>
                        </div>
                    </div>
                </details>
            </div>
            <!-- Botón para Resetear Filtros -->
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
  </AppLayout>
</template>
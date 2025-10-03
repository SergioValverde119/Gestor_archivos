<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, computed, onMounted } from 'vue';
import { debounce } from 'lodash';
import { Eye, Edit, Search, Download, ArrowRight, ArrowLeft, ChevronDown } from 'lucide-vue-next';
import OficioActionButtons from './Partials/OficioActionButtons.vue';

// --- Definición de Tipos para los Datos del Controlador ---
interface Documento { id: number; ruta_almacenamiento: string; }
interface Area { id: number; nombre: string; }
interface Expediente { id: number; numero_expediente: string; areas: Area[]; }
interface User { id: number; name: string; }
interface Permission { user: User; }
interface Oficio {
  id: number;
  tipo: 'entrada' | 'salida';
  folio_externo: string | null;
  folio_salida: string | null;
  folio_interno: string | null;
  remitente: string | null;
  destinatario: string | null;
  asunto: string;
  descripcion: string | null;
  status: string;
  prioridad: string;
  fecha_recepcion: string | null;
  fecha_limite: string | null;
  tiene_turno_dgaf: boolean;
  folio_turno_dgaf: string | null;
  fecha_turno_dgaf: string | null;
  expediente: Expediente | null;
  recibidoPor: User | null;
  created_at: string;
  documentoPrincipal?: Documento | null;
  documentos: Documento[];
  permissions: Permission[];
  respuestaA: { id: number; folio_interno: string; } | null;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedOficios { data: Oficio[]; links: PaginationLink[]; }

const props = defineProps<{
  oficios: PaginatedOficios;
  filters: { search?: string; };
}>();

// --- Lógica de Búsqueda ---
const searchQuery = ref(props.filters.search || '');
watch(searchQuery, debounce(() => {
  router.get('/oficios', { search: searchQuery.value }, { preserveState: true, replace: true });
}, 300));

// --- Lógica para Columnas Personalizables ---
const allColumns = ref([
    { key: 'acciones', label: 'Acciones' },
    { key: 'tipo', label: 'Tipo' },
    { key: 'folio', label: 'Folio' },
    { key: 'asunto', label: 'Asunto' },
    { key: 'status', label: 'Estado' },
    { key: 'prioridad', label: 'Prioridad' },
    { key: 'fechaRegistro', label: 'Fecha Reg.' },
    { key: 'folioInterno', label: 'Folio Interno' },
    { key: 'expediente', label: 'Expediente' },
    { key: 'remitente_destinatario', label: 'Remitente / Dest.' },
    { key: 'descripcion', label: 'Descripción' },
    { key: 'fechaRecepcion', label: 'Fecha Recep.' },
    { key: 'fechaLimite', label: 'Fecha Límite' },
    { key: 'registradoPor', label: 'Registrado Por' },
    { key: 'areas', label: 'Áreas' },
    { key: 'asignadoA', label: 'Asignado A' },
    { key: 'respondeA', label: 'Responde A' },
    { key: 'turnoDGAF', label: 'Turno DGAF' },
    { key: 'anexos', label: 'Anexos' },
]);

const visibleColumns = ref<string[]>([]);

onMounted(() => {
    const saved = localStorage.getItem('visibleOficioColumns');
    if (saved) {
        visibleColumns.value = JSON.parse(saved);
    } else {
        visibleColumns.value = ['acciones', 'tipo', 'folio', 'asunto', 'status', 'prioridad', 'fechaRegistro'];
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

// --- Funciones de Formateo y Estilos ---
const formatDate = (dateString: string | null): string => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' });
};
const statusClasses = (status: string) => {
    if (!status) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    switch (status.toLowerCase()) {
        case 'pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'en proceso': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'resuelto': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
const priorityClasses = (priority: string) => {
    if (!priority) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    switch (priority.toLowerCase()) {
        case 'urgente': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'extremadamente urgente': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
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
          <input type="text" v-model="searchQuery" placeholder="Buscar..." class="w-full rounded-md shadow-sm pl-10" />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="h-5 w-5 text-gray-400" />
          </div>
        </div>

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
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th v-for="header in visibleHeaders" :key="header.key" class="px-6 py-3 text-left text-xs font-medium uppercase">
                    {{ header.label }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="oficios.data.length === 0">
                <td :colspan="visibleHeaders.length" class="px-6 py-4 text-center text-sm">No se encontraron oficios.</td>
              </tr>
              <tr v-for="oficio in oficios.data" :key="oficio.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td v-for="col in visibleHeaders" :key="col.key" class="px-6 py-4 whitespace-nowrap text-sm">
                    <template v-if="col.key === 'acciones'">
                      <OficioActionButtons :oficio="oficio" />
                    </template>
                    <template v-else-if="col.key === 'tipo'">
                        <span v-if="oficio.tipo === 'entrada'" class="inline-flex items-center text-blue-600"><ArrowRight class="w-4 h-4 mr-1"/> Entrada</span>
                        <span v-else class="inline-flex items-center text-green-600"><ArrowLeft class="w-4 h-4 mr-1"/> Salida</span>
                    </template>
                    <template v-else-if="col.key === 'folio'">{{ oficio.folio_externo || oficio.folio_salida || 'N/A' }}</template>
                    <template v-else-if="col.key === 'asunto'">{{ oficio.asunto }}</template>
                    <template v-else-if="col.key === 'status'"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClasses(oficio.status)">{{ oficio.status }}</span></template>
                    <template v-else-if="col.key === 'prioridad'"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="priorityClasses(oficio.prioridad)">{{ oficio.prioridad }}</span></template>
                    <template v-else-if="col.key === 'fechaRegistro'">{{ formatDate(oficio.created_at) }}</template>
                    <template v-else-if="col.key === 'folioInterno'">{{ oficio.folio_interno || 'N/A' }}</template>
                    <template v-else-if="col.key === 'expediente'"><Link v-if="oficio.expediente" :href="`/expedientes/${oficio.expediente.id}`" class="text-blue-600 hover:underline">{{ oficio.expediente.numero_expediente }}</Link><span v-else>N/A</span></template>
                    <template v-else-if="col.key === 'remitente_destinatario'">{{ oficio.remitente || oficio.destinatario || 'N/A' }}</template>
                    <template v-else-if="col.key === 'descripcion'">{{ oficio.descripcion?.substring(0, 30) }}...</template>
                    <template v-else-if="col.key === 'fechaRecepcion'">{{ formatDate(oficio.fecha_recepcion) }}</template>
                    <template v-else-if="col.key === 'fechaLimite'">{{ formatDate(oficio.fecha_limite) }}</template>
                    <template v-else-if="col.key === 'registradoPor'">{{ oficio.recibidoPor?.name || 'Sistema' }}</template>
                    <template v-else-if="col.key === 'areas'">{{ oficio.expediente?.areas.map(a => a.nombre).join(', ') || 'N/A' }}</template>
                    <template v-else-if="col.key === 'asignadoA'">{{ oficio.permissions.map(p => p.user.name).join(', ') || 'N/A' }}</template>
                    <template v-else-if="col.key === 'respondeA'"><Link v-if="oficio.respuestaA" :href="`/oficios/${oficio.respuestaA.id}`" class="text-blue-600 hover:underline">{{ oficio.respuestaA.folio_interno }}</Link><span v-else>-</span></template>
                    <template v-else-if="col.key === 'turnoDGAF'">
                        <span v-if="oficio.tiene_turno_dgaf" class="text-green-500 font-bold">Sí</span><span v-else>-</span>
                    </template>
                    <template v-else-if="col.key === 'anexos'">{{ oficio.documentos.length - (oficio.documentoPrincipal ? 1 : 0) }}</template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- CORRECCIÓN: Se añade la sección de paginación completa -->
        <div v-if="oficios.links.length > 3" class="flex justify-center mt-6">
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <Link
              v-for="(link, key) in oficios.links"
              :key="key"
              :href="link.url ?? '#'"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              :class="{
                'bg-blue-600 text-white border-blue-600': link.active,
                'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active && link.url,
                'opacity-50 pointer-events-none bg-gray-100 dark:bg-gray-900': !link.url,
              }"
              v-html="link.label"
            />
          </nav>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


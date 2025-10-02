<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { FileText, Edit, Search, Download, ArrowRight, ArrowLeft } from 'lucide-vue-next';

// --- Definición de Tipos para los Datos del Controlador ---
interface Documento {
    ruta_almacenamiento: string;
}

interface Expediente {
    id: number;
    numero_expediente: string;
}

interface User {
    id: number;
    name: string;
}

interface Oficio {
  id: number;
  tipo: 'entrada' | 'salida';
  folio_externo: string | null;
  folio_salida: string | null;
  remitente: string | null;
  destinatario: string | null;
  asunto: string;
  status: string;
  prioridad: string;
  expediente: Expediente | null;
  recibidoPor: User | null;
  created_at: string;
  documentoPrincipal?: Documento | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedOficios {
  data: Oficio[];
  links: PaginationLink[];
}

const props = defineProps<{
  oficios: PaginatedOficios;
  filters: {
    search?: string;
  };
}>();

// --- Lógica de Búsqueda ---
const searchQuery = ref(props.filters.search || '');

watch(searchQuery, debounce(() => {
  router.get('/oficios', 
    { search: searchQuery.value }, 
    { preserveState: true, replace: true }
  );
}, 300));

// Función para formatear fechas
const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-MX', {
    year: 'numeric', month: '2-digit', day: '2-digit'
  });
};

// Clases de color para las etiquetas de estado y prioridad
const statusClasses = computed(() => (status: string) => {
    switch (status) {
        case 'Pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'En Proceso': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'Resuelto': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
});

const priorityClasses = computed(() => (priority: string) => {
    switch (priority) {
        case 'Urgente': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'Extremadamente Urgente': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
});

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
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Buscar por folio, asunto, remitente o destinatario..."
            class="w-full rounded-md shadow-sm pl-10"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="h-5 w-5 text-gray-400" />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Folio</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Asunto</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Remitente / Destinatario</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Prioridad</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Fecha Reg.</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="oficios.data.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                  No se encontraron oficios.
                </td>
              </tr>
              <tr v-for="oficio in oficios.data" :key="oficio.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <span v-if="oficio.tipo === 'entrada'" class="inline-flex items-center text-blue-600 dark:text-blue-400">
                        <ArrowRight class="w-4 h-4 mr-1"/> Entrada
                    </span>
                    <span v-else class="inline-flex items-center text-green-600 dark:text-green-400">
                        <ArrowLeft class="w-4 h-4 mr-1"/> Salida
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ oficio.folio_externo || oficio.folio_salida || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ oficio.asunto }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ oficio.remitente || oficio.destinatario || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClasses(oficio.status)">
                        {{ oficio.status || 'N/A' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="priorityClasses(oficio.prioridad)">
                        {{ oficio.prioridad || 'N/A' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(oficio.created_at) }}</td>
                
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-4 items-center">
                    <!-- Botón de Descarga -->
                    <a 
                        v-if="oficio.documentoPrincipal" 
                        :href="`/storage/${oficio.documentoPrincipal.ruta_almacenamiento}`" 
                        target="_blank"
                        class="text-green-600 hover:text-green-800"
                        title="Descargar Documento Principal"
                    >
                        <Download class="w-5 h-5" />
                    </a>
                    <span v-else class="text-gray-400 text-xs" title="Sin documento adjunto">-</span>
                    
                    <!-- Botón de Detalles -->
                    <Link :href="`/oficios/${oficio.id}`" class="text-blue-600 hover:text-blue-800" title="Ver Detalles del Oficio">
                        <FileText class="w-5 h-5" />
                    </Link>

                    <!-- Botón de Editar -->
                    <Link :href="`/oficios/${oficio.id}/edit`" class="text-indigo-600 hover:text-indigo-800" title="Editar Oficio">
                      <Edit class="w-5 h-5" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación -->
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

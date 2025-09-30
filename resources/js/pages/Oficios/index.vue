<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { FileText, Edit, Search, Download } from 'lucide-vue-next';

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
  folio_externo: string | null;
  folio_salida: string | null;
  asunto: string;
  status: string;
  expediente: Expediente | null;
  recibidoPor: User | null;
  created_at: string;
  documentoPrincipal?: Documento | null; // <-- CORRECCIÓN: Añadido el documento
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
    year: 'numeric', month: 'long', day: 'numeric'
  });
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

      <!-- Barra de Búsqueda -->
      <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <div class="relative w-full md:w-1/2">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Buscar por folio externo, folio de salida o asunto..."
            class="w-full rounded-md shadow-sm pl-10"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="h-5 w-5 text-gray-400" />
          </div>
        </div>
      </div>

      <!-- Tabla de Resultados -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Folio</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Asunto</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Expediente</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Registrado Por</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Fecha</th>
                <th class="px-6 py-3 text-center text-xs font-medium uppercase">Documento</th>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ oficio.folio_externo || oficio.folio_salida || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ oficio.asunto }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <Link v-if="oficio.expediente" :href="`/expedientes/${oficio.expediente.id}`" class="text-blue-600 hover:underline">
                        {{ oficio.expediente?.numero_expediente }}
                    </Link>
                    <span v-else>N/A</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ oficio.status || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ oficio.recibidoPor?.name || 'Sistema' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(oficio.created_at) }}</td>
                
                <!-- CORRECCIÓN: Nueva celda para el enlace al documento -->
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <a 
                        v-if="oficio.documentoPrincipal" 
                        :href="`/storage/${oficio.documentoPrincipal.ruta_almacenamiento}`" 
                        target="_blank"
                        class="text-green-600 hover:text-green-800 inline-block"
                        title="Ver Documento Principal"
                    >
                        <Download class="w-5 h-5" />
                    </a>
                    <span v-else class="text-gray-400">-</span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-4">
                    <Link :href="`/oficios/${oficio.id}`" class="text-blue-600 hover:text-blue-800" title="Ver Detalles">
                        <FileText class="w-5 h-5" />
                    </Link>
                    <Link :href="`/oficios/${oficio.id}/edit`" class="text-indigo-600 hover:text-indigo-800" title="Editar">
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
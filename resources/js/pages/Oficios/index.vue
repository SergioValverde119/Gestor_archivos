<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

// Definir los tipos de las props que recibirá el componente
interface Documento {
  ruta_almacenamiento: string;
}

interface Oficio {
  id: number;
  folio_oficio: string;
  asunto: string | null;
  status: string;
  fecha_recepcion: string | null;
  documento?: Documento | null; // Propiedad opcional para el documento
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedOficios {
  data: Oficio[];
  links: PaginationLink[];
  current_page: number;
  from: number;
  to: number;
  total: number;
}

const props = defineProps<{
  oficios: PaginatedOficios;
  search?: string;
  field?: string;
}>();

// Objeto de referencias para las rutas
const referencias = {
  oficios: {
    index: () => ({ url: '/oficios' }),
    create: () => ({ url: '/oficios/create' }),
    show: (id: number) => ({ url: `/oficios/${id}` }),
    edit: (id: number) => ({ url: `/oficios/${id}/edit` }),
  },
};

// Variables reactivas para el campo de búsqueda y el campo seleccionado
const searchQuery = ref(props.search || '');
const searchField = ref(props.field || 'folio_oficio');

// Observa los cambios en el campo de búsqueda y en el campo seleccionado
watch([searchQuery, searchField], debounce(() => {
  router.get(
    referencias.oficios.index().url,
    { search: searchQuery.value, field: searchField.value },
    { preserveState: true, replace: true }
  );
}, 300));

// Función para formatear las fechas a un formato legible
const formatDate = (dateString: string | null): string => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

// Definir las migas de pan (breadcrumbs)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Oficios',
    href: referencias.oficios.index().url,
  },
];

// Lista de campos disponibles para la búsqueda
const searchFields = [
  { value: 'folio_oficio', label: 'Folio de Oficio' },
  { value: 'asunto', label: 'Asunto' },
  { value: 'remitente', label: 'Remitente' },
  { value: 'status', label: 'Estado' },
];
</script>

<template>
  <Head title="Oficios" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Lista de Oficios</h1>

      <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <!-- Controles de búsqueda: Menú desplegable y barra de búsqueda -->
        <div class="flex flex-col sm:flex-row gap-4 w-full md:w-2/3">
          <select v-model="searchField" class="w-full sm:w-1/2 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm">
            <option v-for="field in searchFields" :key="field.value" :value="field.value">
              {{ field.label }}
            </option>
          </select>
          <div class="relative w-full sm:w-1/2">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Escribe para buscar..."
              class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm pl-10"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <!-- Icono de lupa SVG -->
              <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Botón para crear un nuevo oficio -->
        <Link :href="referencias.oficios.create().url" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors w-full md:w-auto text-center">
          Crear Nuevo Oficio
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Folio de Oficio
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Asunto
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Estado
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Fecha de Recepción
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Documento
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="oficios.data.length === 0">
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                  No se encontraron oficios que coincidan con la búsqueda.
                </td>
              </tr>
              <tr v-for="oficio in oficios.data" :key="oficio.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ oficio.folio_oficio }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ oficio.asunto || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ oficio.status }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(oficio.fecha_recepcion) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  <a
                    v-if="oficio.documento"
                    :href="`/storage/${oficio.documento.ruta_almacenamiento}`"
                    target="_blank"
                    class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200"
                  >
                    <!-- Icono SVG de documento -->
                    <svg class="h-5 w-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                      <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                    </svg>
                    Ver
                  </a>
                  <span v-else>N/A</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <Link :href="referencias.oficios.show(oficio.id).url" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200">
                      Ver
                    </Link>
                    <Link :href="referencias.oficios.edit(oficio.id).url" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">
                      Editar
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
              v-for="link in oficios.links"
              :key="link.label"
              :href="link.url"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              :class="{
                'bg-blue-500 text-white dark:bg-blue-600 dark:text-white border-blue-500 dark:border-blue-600': link.active,
                'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active,
                'opacity-50 pointer-events-none': !link.url,
              }"
              v-html="link.label"
            >
            </Link>
          </nav>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
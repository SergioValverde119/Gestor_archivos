<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

// Definir los tipos de las props que recibirá el componente
interface Documento {
  id: number;
  ruta_almacenamiento: string;
}

interface Oficio {
  id: number;
  folio_oficio: string;
  asunto: string | null;
  status: string;
  fecha_recepcion: string | null;
  remitente: string | null;
  dependencia_emisora: string | null;
  dependencia_turno: string | null;
  documento?: Documento | null; // Propiedad opcional para el documento
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  oficio: Oficio;
}>();

// Objeto de referencias para las rutas
const referencias = {
  oficios: {
    index: () => ({ url: '/oficios' }),
  },
};

// Definir las migas de pan (breadcrumbs)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Oficios',
    href: referencias.oficios.index().url,
  },
  {
    title: 'Detalles del Oficio',
    href: '#',
  },
];

// Función para formatear las fechas a un formato legible
const formatDate = (dateString: string | null): string => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return date.toLocaleDateString('es-ES', options);
};

// Determinar si el documento existe para mostrar el botón
const hasDocumento = computed(() => {
  return props.oficio.documento && props.oficio.documento.ruta_almacenamiento;
});

// URL completa del documento
const documentoUrl = computed(() => {
  if (hasDocumento.value) {
    return `/storage/${props.oficio.documento?.ruta_almacenamiento}`;
  }
  return '#';
});
</script>

<template>
  <Head :title="`Detalles: ${oficio.folio_oficio}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        Detalles del Oficio: {{ oficio.folio_oficio }}
      </h1>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <!-- Contenedor para mostrar la información del oficio -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Folio de Oficio:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.folio_oficio }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Asunto:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.asunto || 'N/A' }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Estado:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.status }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Remitente:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.remitente || 'N/A' }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Dependencia Emisora:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.dependencia_emisora || 'N/A' }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Dependencia de Turno:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ oficio.dependencia_turno || 'N/A' }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Fecha de Recepción:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ formatDate(oficio.fecha_recepcion) }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Fecha de Creación:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ formatDate(oficio.created_at) }}</p>
          </div>
          <div>
            <p class="font-semibold text-gray-600 dark:text-gray-400">Última Actualización:</p>
            <p class="text-gray-900 dark:text-gray-100">{{ formatDate(oficio.updated_at) }}</p>
          </div>
        </div>

        <!-- Sección de acciones -->
        <div class="mt-8 flex flex-wrap gap-4 justify-start items-center">
          <!-- Botón para ver el documento -->
          <a
            v-if="hasDocumento"
            :href="documentoUrl"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            <!-- Icono SVG de documento -->
            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
            </svg>
            Ver Documento
          </a>
          
          <!-- Botón de regreso -->
          <Link :href="referencias.oficios.index().url" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Regresar
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
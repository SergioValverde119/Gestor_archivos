<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Definir los tipos de las props que recibirá el componente
interface Oficio {
  id: number;
  folio_oficio: string;
  remitente: string | null;
  asunto: string | null;
  situacion: string | null;
  folio_interno: string | null;
  fecha_recepcion: string | null;
  fecha_limite: string | null;
  prioridad: { id: number; nombre: string } | null;
  area: { id: number; nombre: string } | null;
  asignado_a_user: { id: number; name: string } | null;
  status: string;
  url_archivo: string | null;
}

const props = defineProps<{
  oficio: Oficio;
}>();

// Objeto de referencias para las rutas
const referencias = {
  oficios: {
    index: () => ({ url: '/oficios' }),
    edit: (id: number) => ({ url: `/oficios/${id}/edit` }),
  },
};

// Definir las migas de pan (breadcrumbs)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Oficios',
    href: referencias.oficios.index().url,
  },
  {
    title: props.oficio.folio_oficio,
    href: '', // La página actual no necesita un enlace
  },
];
</script>

<template>
  <Head :title="`Oficio: ${oficio.folio_oficio}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Detalles del Oficio</h1>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Folio de Oficio -->
          <div class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Folio de Oficio</span>
            <span class="block text-lg font-semibold text-gray-900 dark:text-gray-100">{{ oficio.folio_oficio }}</span>
          </div>

          <!-- Estado -->
          <div class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Estado</span>
            <span class="block text-lg font-semibold text-gray-900 dark:text-gray-100">{{ oficio.status }}</span>
          </div>

          <!-- Remitente (Opcional) -->
          <div v-if="oficio.remitente" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Remitente</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.remitente }}</span>
          </div>
          
          <!-- Asunto (Opcional) -->
          <div v-if="oficio.asunto" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Asunto</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.asunto }}</span>
          </div>

          <!-- Situación (Opcional) -->
          <div v-if="oficio.situacion" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Situación</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.situacion }}</span>
          </div>

          <!-- Folio Interno (Opcional) -->
          <div v-if="oficio.folio_interno" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Folio Interno</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.folio_interno }}</span>
          </div>
          
          <!-- Fecha de Recepción (Opcional) -->
          <div v-if="oficio.fecha_recepcion" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Recepción</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.fecha_recepcion }}</span>
          </div>

          <!-- Fecha Límite (Opcional) -->
          <div v-if="oficio.fecha_limite" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Límite</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.fecha_limite }}</span>
          </div>
          
          <!-- Prioridad (Opcional) -->
          <div v-if="oficio.prioridad" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Prioridad</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.prioridad.nombre }}</span>
          </div>

          <!-- Área (Opcional) -->
          <div v-if="oficio.area" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Área</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.area.nombre }}</span>
          </div>
          
          <!-- Asignado a (Opcional) -->
          <div v-if="oficio.asignado_a_user" class="border-b dark:border-gray-700 pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Asignado a</span>
            <span class="block text-lg text-gray-800 dark:text-gray-200">{{ oficio.asignado_a_user.name }}</span>
          </div>
          
          <!-- Archivo Adjunto (Opcional) -->
          <div v-if="oficio.url_archivo" class="pb-4">
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Archivo Adjunto</span>
            <a :href="oficio.url_archivo" target="_blank" class="text-blue-500 dark:text-blue-400 hover:underline">
              Ver/Descargar Archivo
            </a>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <Link :href="referencias.oficios.index().url" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-semibold py-2 px-4 rounded-lg">
            Regresar
          </Link>
          <Link :href="referencias.oficios.edit(oficio.id).url" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors">
            Editar Oficio
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
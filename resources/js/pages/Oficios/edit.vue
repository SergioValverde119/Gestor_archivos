<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Definir los tipos de las props que recibirá el componente
interface Oficio {
  id: number;
  folio_oficio: string;
  asunto: string | null;
  status: string;
  fecha_recepcion: string | null;
  remitente: string | null;
  dependencia_emisora: string | null;
  dependencia_turno: string | null;
  documento: {
    ruta_almacenamiento: string;
  } | null;
}

const props = defineProps<{
  oficio: Oficio;
  // Propiedad opcional para recibir errores de validación desde el servidor
  errors: Record<string, string>; 
}>();

// Objeto de referencias para las rutas
const referencias = {
  oficios: {
    index: () => ({ url: '/oficios' }),
    show: (id: number) => ({ url: `/oficios/${id}` }),
  },
};

// Crear un formulario reactivo con useForm
// Inicializamos el formulario con los datos del oficio que se está editando
const form = useForm({
  folio_oficio: props.oficio.folio_oficio,
  asunto: props.oficio.asunto || '',
  status: props.oficio.status,
  fecha_recepcion: props.oficio.fecha_recepcion || '',
  remitente: props.oficio.remitente || '',
  dependencia_emisora: props.oficio.dependencia_emisora || '',
  dependencia_turno: props.oficio.dependencia_turno || '',
  documento: null, // Campo para el nuevo archivo que se subirá
});

// Definir las migas de pan (breadcrumbs)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Oficios',
    href: referencias.oficios.index().url,
  },
  {
    title: 'Detalles del Oficio',
    href: referencias.oficios.show(props.oficio.id).url,
  },
  {
    title: 'Editar Oficio',
    href: '#',
  },
];

// Función para enviar el formulario a la ruta de actualización
const submit = () => {
  // Envia un formulario PUT a la ruta de actualización
  // Inertia maneja el tipo de solicitud automáticamente
  form.post(referencias.oficios.show(props.oficio.id).url, {
    _method: 'put',
    // La opción 'forceFormData' es necesaria para que Inertia envíe los datos
    // del formulario como FormData, lo cual es requerido para la carga de archivos.
    forceFormData: true, 
    onSuccess: () => {
      console.log('Oficio actualizado con éxito!');
      // Puedes agregar lógica adicional aquí, como mostrar un mensaje de éxito
    },
    onError: (errors) => {
      console.error('Error al actualizar el oficio:', errors);
      // Los errores se manejarán automáticamente por Inertia y estarán disponibles
      // en la propiedad `errors` de las props
    },
  });
};

// Obtenemos la URL para mostrar el documento actual
const currentDocumentUrl = ref(
  props.oficio.documento 
    ? `/storage/${props.oficio.documento.ruta_almacenamiento}` 
    : null
);
</script>

<template>
  <Head :title="`Editar: ${oficio.folio_oficio}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        Editar Oficio: {{ oficio.folio_oficio }}
      </h1>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Campo: Folio de Oficio -->
          <div>
            <label for="folio_oficio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio de Oficio</label>
            <input
              type="text"
              id="folio_oficio"
              v-model="form.folio_oficio"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.folio_oficio }"
              autocomplete="off"
            />
            <p v-if="errors.folio_oficio" class="mt-2 text-sm text-red-600">{{ errors.folio_oficio }}</p>
          </div>

          <!-- Campo: Asunto -->
          <div>
            <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
            <input
              type="text"
              id="asunto"
              v-model="form.asunto"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.asunto }"
              autocomplete="off"
            />
            <p v-if="errors.asunto" class="mt-2 text-sm text-red-600">{{ errors.asunto }}</p>
          </div>

          <!-- Campo: Estado -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
            <input
              type="text"
              id="status"
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.status }"
              autocomplete="off"
            />
            <p v-if="errors.status" class="mt-2 text-sm text-red-600">{{ errors.status }}</p>
          </div>

          <!-- Campo: Fecha de Recepción -->
          <div>
            <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Recepción</label>
            <input
              type="date"
              id="fecha_recepcion"
              v-model="form.fecha_recepcion"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.fecha_recepcion }"
              autocomplete="off"
            />
            <p v-if="errors.fecha_recepcion" class="mt-2 text-sm text-red-600">{{ errors.fecha_recepcion }}</p>
          </div>

          <!-- Campo: Remitente -->
          <div>
            <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</label>
            <input
              type="text"
              id="remitente"
              v-model="form.remitente"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.remitente }"
              autocomplete="off"
            />
            <p v-if="errors.remitente" class="mt-2 text-sm text-red-600">{{ errors.remitente }}</p>
          </div>

          <!-- Campo: Dependencia Emisora -->
          <div>
            <label for="dependencia_emisora" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dependencia Emisora</label>
            <input
              type="text"
              id="dependencia_emisora"
              v-model="form.dependencia_emisora"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.dependencia_emisora }"
              autocomplete="off"
            />
            <p v-if="errors.dependencia_emisora" class="mt-2 text-sm text-red-600">{{ errors.dependencia_emisora }}</p>
          </div>
          
          <!-- Campo: Dependencia de Turno -->
          <div>
            <label for="dependencia_turno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dependencia de Turno</label>
            <input
              type="text"
              id="dependencia_turno"
              v-model="form.dependencia_turno"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.dependencia_turno }"
              autocomplete="off"
            />
            <p v-if="errors.dependencia_turno" class="mt-2 text-sm text-red-600">{{ errors.dependencia_turno }}</p>
          </div>

          <!-- Campo: Documento (subida de archivo) -->
          <div>
            <label for="documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Documento (opcional)</label>
            <input
              type="file"
              id="documento"
              @input="e => form.documento = e.target.files[0]"
              class="mt-1 block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100"
            />
            <p v-if="errors.documento" class="mt-2 text-sm text-red-600">{{ errors.documento }}</p>
            <p v-if="currentDocumentUrl" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Documento actual: <a :href="currentDocumentUrl" target="_blank" class="text-blue-500 hover:underline">Ver documento</a>
            </p>
            <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay documento asociado actualmente.</p>
          </div>

          <!-- Botones de acción -->
          <div class="flex items-center justify-end space-x-4">
            <Link :href="referencias.oficios.show(oficio.id).url" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
              Cancelar
            </Link>
            <button
              type="submit"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              :disabled="form.processing"
            >
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

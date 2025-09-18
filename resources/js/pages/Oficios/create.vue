<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Objeto de referencias para las rutas, simulando un módulo global o importado.
const referencias = {
  oficios: {
    index: () => ({ url: '/oficios' }),
    store: () => ({ url: '/oficios' }),
    create: () => ({ url: '/oficios/create' }),
  },
};

const props = defineProps<{
  prioridades: { id: number; nombre: string }[];
  areas: { id: number; nombre: string }[];
  users: { id: number; name: string }[];
}>();

// Formulario de Inertia, con los campos inicializados
const form = useForm({
  folio_oficio: '',
  remitente: '',
  asunto: '',
  situacion: '',
  folio_interno: '',
  fecha_recepcion: '',
  fecha_limite: '',
  prioridad_id: null,
  area_id: null,
  asignado_a_user_id: null,
  status: 'Pendiente', // Establecer un estado inicial predeterminado
  archivo: null as File | null,
});

// Variables para el mensaje de éxito
const showSuccessMessage = ref(false);

// Enviar formulario para crear un nuevo oficio
const submit = () => {
  form.post(referencias.oficios.store().url, {
    onSuccess: () => {
      // Limpiar los campos del formulario y los errores
      form.reset();
      showSuccessMessage.value = true;
      // Ocultar el mensaje de éxito después de 5 segundos
      setTimeout(() => {
        showSuccessMessage.value = false;
      }, 5000);
    },
  });
};

// Manejar la selección del archivo
const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    form.archivo = target.files[0];
  }
};

// Definir las migas de pan (breadcrumbs)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Oficios',
    href: referencias.oficios.index().url,
  },
  {
    title: 'Crear',
    href: referencias.oficios.create().url,
  },
];
</script>

<template>
  <Head title="Crear Oficio" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Crear Nuevo Oficio</h1>
      <!-- Mensaje de éxito -->
      <div v-if="showSuccessMessage" class="bg-green-100 dark:bg-green-700 border border-green-400 dark:border-green-600 text-green-700 dark:text-white px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">¡Oficio creado!</strong>
        <span class="block sm:inline">El oficio ha sido guardado exitosamente.</span>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="folio_oficio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio de Oficio</label>
              <input type="text" id="folio_oficio" v-model="form.folio_oficio" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.folio_oficio" class="text-red-500 text-sm mt-1">{{ form.errors.folio_oficio }}</div>
            </div>

            <div>
              <label for="archivo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Archivo Adjunto</label>
              <input type="file" id="archivo" @change="handleFileChange" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.archivo" class="text-red-500 text-sm mt-1">{{ form.errors.archivo }}</div>
            </div>

            <!-- Campos opcionales -->
            <div>
              <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente (Opcional)</label>
              <input type="text" id="remitente" v-model="form.remitente" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.remitente" class="text-red-500 text-sm mt-1">{{ form.errors.remitente }}</div>
            </div>

            <div>
              <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto (Opcional)</label>
              <input type="text" id="asunto" v-model="form.asunto" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
            </div>

            <div>
              <label for="situacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Situación (Opcional)</label>
              <textarea id="situacion" v-model="form.situacion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm"></textarea>
              <div v-if="form.errors.situacion" class="text-red-500 text-sm mt-1">{{ form.errors.situacion }}</div>
            </div>

            <div>
              <label for="folio_interno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Interno (Opcional)</label>
              <input type="text" id="folio_interno" v-model="form.folio_interno" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.folio_interno" class="text-red-500 text-sm mt-1">{{ form.errors.folio_interno }}</div>
            </div>

            <div>
              <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Recepción (Opcional)</label>
              <input type="date" id="fecha_recepcion" v-model="form.fecha_recepcion" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.fecha_recepcion" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_recepcion }}</div>
            </div>

            <div>
              <label for="fecha_limite" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Límite (Opcional)</label>
              <input type="date" id="fecha_limite" v-model="form.fecha_limite" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm" />
              <div v-if="form.errors.fecha_limite" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_limite }}</div>
            </div>

            <div>
              <label for="prioridad_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad (Opcional)</label>
              <select id="prioridad_id" v-model="form.prioridad_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm">
                <option :value="null" disabled>Selecciona una prioridad</option>
                <option v-for="prioridad in prioridades" :key="prioridad.id" :value="prioridad.id">
                  {{ prioridad.nombre }}
                </option>
              </select>
              <div v-if="form.errors.prioridad_id" class="text-red-500 text-sm mt-1">{{ form.errors.prioridad_id }}</div>
            </div>

            <div>
              <label for="area_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Área (Opcional)</label>
              <select id="area_id" v-model="form.area_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm">
                <option :value="null" disabled>Selecciona un área</option>
                <option v-for="area in areas" :key="area.id" :value="area.id">
                  {{ area.nombre }}
                </option>
              </select>
              <div v-if="form.errors.area_id" class="text-red-500 text-sm mt-1">{{ form.errors.area_id }}</div>
            </div>

            <div>
              <label for="asignado_a_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asignado a (Opcional)</label>
              <select id="asignado_a_user_id" v-model="form.asignado_a_user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm">
                <option :value="null" disabled>Selecciona un usuario</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <div v-if="form.errors.asignado_a_user_id" class="text-red-500 text-sm mt-1">{{ form.errors.asignado_a_user_id }}</div>
            </div>
            
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
              <select id="status" v-model="form.status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm">
                <option value="Pendiente">Pendiente</option>
                <option value="En Proceso">En Proceso</option>
                <option value="Completado">Completado</option>
              </select>
              <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-4">
            <Link :href="referencias.oficios.index().url" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-semibold py-2 px-4 rounded-lg">
              Cancelar
            </Link>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors"
              :disabled="form.processing"
            >
              Crear Oficio
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
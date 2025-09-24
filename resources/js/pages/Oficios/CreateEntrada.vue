<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

// Definición de tipos para las props
interface Area {
    id: number;
    nombre: string;
}

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
  areas: Area[];
  users: User[]; // Lista filtrada de operativos para permisos
  allUsers: User[]; // Lista completa para "Recibido Por"
  nextFolioInterno: string;
}>();

const authUser = computed(() => usePage().props.auth.user as User);

const sortedAllUsers = computed(() => {
    if (!props.allUsers) return [];
    const currentUser = props.allUsers.find(u => u.id === authUser.value.id);
    const otherUsers = props.allUsers.filter(u => u.id !== authUser.value.id);
    return currentUser ? [currentUser, ...otherUsers] : props.allUsers;
});

const form = useForm({
  folio_interno: props.nextFolioInterno,
  folio_externo: '',
  remitente: '',
  asunto: '',
  descripcion: '',
  fecha_recepcion: new Date().toISOString().split('T')[0],
  prioridad: 'Ordinario' as 'Ordinario' | 'Urgente' | 'Extremadamente Urgente',
  status: 'Pendiente',
  documento_principal: null as File | null,
  anexos: [] as File[],
  recibido_por_user_id: authUser.value ? authUser.value.id : null,
  area_ids: [] as number[],
  asignaciones: [] as { user_id: number | null; permission: 'editor' | 'visualizador' }[],
});

// -- NUEVA LÓGICA DE ÉXITO --
const submissionSuccessful = ref(false);

const submit = () => {
  form.post('/oficios/entrada', {
      onSuccess: () => {
          // Al tener éxito, simplemente mostramos la pantalla de éxito.
          submissionSuccessful.value = true;
      },
  });
};

const handleAnexosChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.anexos = Array.from(target.files);
    }
}

const addAsignacion = () => {
    if (props.users.length > 0) {
        form.asignaciones.push({ user_id: null, permission: 'visualizador' });
    }
}

const removeAsignacion = (index: number) => {
    form.asignaciones.splice(index, 1);
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: 'Registrar Oficio de Entrada', href: '/oficios/entrada/registrar' },
];
</script>

<template>
  <Head title="Registrar Oficio de Entrada" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Registrar Oficio de Entrada</h1>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        
        <!-- --- NUEVO: Panel de Éxito --- -->
        <div v-if="submissionSuccessful" class="text-center py-10">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">¡Éxito!</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">El oficio ha sido registrado correctamente.</p>
            <div class="mt-6">
                <Link
                    href="/oficios/entrada/registrar"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Registrar Nuevo Oficio
                </Link>
            </div>
        </div>

        <!-- Formulario (ahora se oculta al tener éxito) -->
        <form v-else @submit.prevent="submit" class="space-y-6">
          <!-- SECCIÓN DE OFICIO -->
          <div class="border-b dark:border-gray-700 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Datos del Oficio</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div>
                <label for="folio_externo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Externo (el que viene en el documento)</label>
                <input type="text" id="folio_externo" v-model="form.folio_externo" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.folio_externo" class="text-red-500 text-sm mt-1">{{ form.errors.folio_externo }}</div>
              </div>

              <div>
                <label for="folio_interno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Interno (Generado)</label>
                <input type="text" id="folio_interno" v-model="form.folio_interno" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
              </div>
              
              <div class="md:col-span-2">
                <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</label>
                <input type="text" id="remitente" v-model="form.remitente" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.remitente" class="text-red-500 text-sm mt-1">{{ form.errors.remitente }}</div>
              </div>

              <div class="md:col-span-2">
                <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                <input type="text" id="asunto" v-model="form.asunto" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
              </div>

              <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
                <textarea id="descripcion" v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md shadow-sm"></textarea>
              </div>
              
              <div>
                <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Recepción</label>
                <input type="date" id="fecha_recepcion" v-model="form.fecha_recepcion" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.fecha_recepcion" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_recepcion }}</div>
              </div>

              <div>
                <label for="prioridad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad</label>
                <select id="prioridad" v-model="form.prioridad" class="mt-1 block w-full rounded-md shadow-sm">
                  <option>Ordinario</option>
                  <option>Urgente</option>
                  <option>Extremadamente Urgente</option>
                </select>
              </div>

               <div>
                <label for="recibido_por_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recibido Por</label>
                <select id="recibido_por_user_id" v-model="form.recibido_por_user_id" required class="mt-1 block w-full rounded-md shadow-sm">
                  <option v-for="user in sortedAllUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
                <div v-if="form.errors.recibido_por_user_id" class="text-red-500 text-sm mt-1">{{ form.errors.recibido_por_user_id }}</div>
              </div>
            </div>
          </div>

          <!-- SECCIÓN DE DOCUMENTOS -->
          <div class="border-b dark:border-gray-700 pb-6">
             <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Adjuntar Documentos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="documento_principal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Documento Principal (Requerido)</label>
                    <input type="file" id="documento_principal" @input="form.documento_principal = ($event.target as HTMLInputElement).files?.[0] ?? null" required class="mt-1 block w-full text-sm" />
                    <div v-if="form.errors.documento_principal" class="text-red-500 text-sm mt-1">{{ form.errors.documento_principal }}</div>
                </div>
                <div>
                    <label for="anexos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anexos (Opcional, puede seleccionar varios)</label>
                    <input type="file" id="anexos" @change="handleAnexosChange" multiple class="mt-1 block w-full text-sm" />
                    <div v-if="form.errors.anexos" class="text-red-500 text-sm mt-1">{{ form.errors.anexos }}</div>
                </div>
            </div>
          </div>
          
           <!-- SECCIÓN DE ÁREAS -->
          <div class="border-b dark:border-gray-700 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Áreas Involucradas</h2>
            <div>
                <label for="area_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seleccione las áreas que deben tener acceso a este caso (Ctrl+Click para varios)</label>
                <select id="area_ids" v-model="form.area_ids" multiple required class="mt-1 block w-full rounded-md shadow-sm">
                    <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                </select>
                <div v-if="form.errors.area_ids" class="text-red-500 text-sm mt-1">{{ form.errors.area_ids }}</div>
            </div>
          </div>

          <!-- SECCIÓN DE ASIGNACIONES -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Asignar Permisos a Operativos (Opcional)</h2>
            <div v-for="(asignacion, index) in form.asignaciones" :key="index" class="flex items-center space-x-4 mb-2">
                <select v-model="asignacion.user_id" class="block w-1/2 rounded-md shadow-sm">
                    <option :value="null" disabled>Seleccione un operativo</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
                <select v-model="asignacion.permission" class="block w-1/3 rounded-md shadow-sm">
                    <option value="visualizador">Visualizador</option>
                    <option value="editor">Editor</option>
                </select>
                <button type="button" @click="removeAsignacion(index)" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
             <button type="button" @click="addAsignacion" class="mt-2 text-sm text-blue-600 hover:text-blue-800" v-if="users.length > 0">+ Añadir Asignación</button>
             <div v-if="form.errors.asignaciones" class="text-red-500 text-sm mt-1">{{ form.errors.asignaciones }}</div>
          </div>

          <div class="mt-6 flex justify-end space-x-4">
            <Link href="/oficios" class="px-4 py-2 rounded-md text-sm font-medium">Cancelar</Link>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors"
              :disabled="form.processing"
            >
              Registrar Oficio
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
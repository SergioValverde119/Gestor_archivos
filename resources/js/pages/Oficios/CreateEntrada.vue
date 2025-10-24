<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

// --- Se importan los tipos desde el archivo central ---
import { 
    type BreadcrumbItem, 
    type Area, 
    type SimpleUser,
    type User 
} from '@/types';

// Importa los componentes
import OficioDataSection from './Partials/OficioDataSection.vue';
import TurnoDgafSection from './Partials/TurnoDgafSection.vue';
import DocumentosSection from './Partials/DocumentosSection.vue';
import AreasSection from './Partials/AreasSection.vue';
import AsignacionesSection from './Partials/AsignacionesSection.vue';
import SuccessPanel from './Partials/SuccessPanel.vue';
import FormErrors from './Partials/FormErrors.vue';
import AsuntoDescripcionSection from './Partials/AsuntoDescripcionSection.vue';

// --- (Las definiciones locales de tipos se han eliminado) ---

const props = defineProps<{
  areas: Area[];
  users: SimpleUser[];
  allUsers: SimpleUser[];
  nextFolioInterno: string;
  flash?: { success?: string; }
}>();

const flash = computed(() => usePage().props.flash as { success?: string });
const submissionSuccessful = computed(() => !!flash.value?.success);
const authUser = computed(() => usePage().props.auth.user as User);


const form = useForm({
  folio_interno: props.nextFolioInterno,
  folio_externo: '',
  remitente: '',
  asunto: '',
  descripcion: '',
  fecha_recepcion: new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().split('T')[0],
  fecha_limite: null as string | null,
  prioridad: 'Ordinario' as 'Ordinario' | 'Urgente' | 'Extremadamente Urgente',
  status: 'Pendiente',
  documento_principal: null as File | null,
  anexos: [] as File[],
  recibido_por_user_id: authUser.value ? authUser.value.id : null,
  area_ids: [null] as (number|null)[],
  asignaciones: [] as { user_id: number | null; permission: 'editor' | 'visualizador' }[],
  tiene_turno_dgaf: false,
  folio_turno_dgaf: '',
  fecha_turno_dgaf: '',
});

const submit = () => {
    form.transform(data => ({
      ...data,
      area_ids: data.area_ids.filter(id => id !== null),
  })).post('/oficios/entrada');
};

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
        
        <SuccessPanel 
            v-if="submissionSuccessful"
            title="¡Éxito!"
            :message="flash?.success ?? 'El oficio ha sido registrado correctamente.'"
        >
            <Link
                href="/oficios/entrada/registrar"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
            >
                Registrar Nuevo Oficio
            </Link>
            <Link
                href="/oficios"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
            >
                Volver a la Lista
            </Link>
        </SuccessPanel>

        <form v-else @submit.prevent="submit" class="space-y-6">
          <FormErrors :form="form" />
          <OficioDataSection :form="form" :all-users="props.allUsers" />
          <AsuntoDescripcionSection :form="form" />
          <TurnoDgafSection :form="form" />
          <DocumentosSection :form="form" />
          <AreasSection :form="form" :areas="props.areas" />
          <AsignacionesSection :form="form" :users="props.users" />

          <!-- Botones de Acción -->
          <div class="mt-6 flex justify-end space-x-4">
            <Link href="/oficios" class="px-4 py-2 rounded-md text-sm font-medium">Cancelar</Link>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md"
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

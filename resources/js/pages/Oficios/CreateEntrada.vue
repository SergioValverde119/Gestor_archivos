<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

// Importa los componentes
import OficioDataSection from './Partials/OficioDataSection.vue';
import TurnoDgafSection from './Partials/TurnoDgafSection.vue';
import DocumentosSection from './Partials/DocumentosSection.vue';
import AreasSection from './Partials/AreasSection.vue';
import AsignacionesSection from './Partials/AsignacionesSection.vue';
import SuccessPanel from './Partials/SuccessPanel.vue';
import FormErrors from './Partials/FormErrors.vue';
import AsuntoDescripcionSection from './Partials/AsuntoDescripcionSection.vue';
import OficioRespuestaSection from './Partials/OficioRespuestaSection.vue';

// --- Definiciones de Tipos ---
interface Area { id: number; nombre: string; }
interface User { id: number; name: string; }
interface SearchableOficio { id: number; folio_interno: string | null; folio_externo: string | null; folio_salida: string | null; asunto: string; }

const props = defineProps<{
  areas: Area[];
  users: User[];
  searchableOficios: SearchableOficio[];
  allUsers: User[];
  nextFolioInterno: string;
  flash?: { success?: string; }
}>();

// --- CORRECCIÓN: Usar una propiedad computada para la reactividad ---
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
  fecha_limite: '' as string | null,
  prioridad: 'Ordinario' as 'Ordinario' | 'Urgente' | 'Extremadamente Urgente',
  status: 'Pendiente',
  documento_principal: null as File | null,
  anexos: [] as File[],
  recibido_por_user_id: authUser.value ? authUser.value.id : null,
  area_ids: [],
  oficio_respuesta_id: null as number | null,
  asignaciones: [],
  tiene_turno_dgaf: false,
  folio_turno_dgaf: '',
  fecha_turno_dgaf: '',
});

const submit = () => {
  form.post('/oficios/entrada');
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
            :message="flash.success ?? ''"
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
          <OficioRespuestaSection :form="form" :searchable-oficios="props.searchableOficios" />
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

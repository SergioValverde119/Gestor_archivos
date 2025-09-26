<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

// Importa las secciones del formulario
import OficioDataSection from './Partials/OficioDataSection.vue';
import DocumentosSection from './Partials/DocumentosSection.vue';
import AreasSection from './Partials/AreasSection.vue';
import AsignacionesSection from './Partials/AsignacionesSection.vue';

// --- Definiciones de Tipos ---
interface Area { id: number; nombre: string; }
interface User { id: number; name: string; }

const props = defineProps<{
  areas: Area[];
  users: User[];
  allUsers: User[];
  nextFolioInterno: string;
  flash: { success?: string; }
}>();

const submissionSuccessful = ref(!!props.flash?.success);
const authUser = computed(() => usePage().props.auth.user as User);

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
  // --- CORRECCIÓN: La estructura ahora es un array simple de IDs ---
  area_ids: [] as number[],
  asignaciones: [] as { user_id: number | null; permission: 'editor' | 'visualizador' }[],
});

const submit = () => {
  // --- CORRECCIÓN: Ya no se necesita el método transform ---
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
        
        <div v-if="submissionSuccessful" class="text-center py-10">
            <!-- Panel de Éxito -->
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium">¡Éxito!</h3>
            <p class="mt-1 text-sm">El oficio ha sido registrado correctamente.</p>
            <div class="mt-6">
                <Link
                    href="/oficios/entrada/registrar"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    Registrar Nuevo Oficio
                </Link>
            </div>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-6">
          
          <OficioDataSection :form="form" :all-users="props.allUsers" />
          <DocumentosSection :form="form" />
          <AreasSection :form="form" :areas="props.areas" />
          <AsignacionesSection :form="form" :users="props.users" />

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
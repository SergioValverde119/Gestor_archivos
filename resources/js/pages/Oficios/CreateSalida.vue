<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import OficioRespuestaSection from './Partials/OficioRespuestaSection.vue';
import  FormErrors  from './Partials/FormErrors.vue';
//Componentes de oficio
import AsignacionesSection from './Partials/AsignacionesSection.vue';
import AreasSection from './Partials/AreasSection.vue';
import SuccessPanel from './Partials/SuccessPanel.vue';

// --- Definiciones de Tipos ---
interface Area { id: number; nombre: string; }
interface User { id: number; name: string; }
interface SearchableOficio { id: number; folio_interno: string | null; folio_externo: string | null; folio_salida: string | null; asunto: string; }

const props = defineProps<{
  areas: Area[];
  users: User[]; // Operativos
  searchableOficios: SearchableOficio[];
  nextFolioSalida: string;
  nextFolioInterno: string;
  flash?: { success?: string; }
}>();

// --- Lógica de Éxito ---


const flash = computed(() => usePage().props.flash as { success?: string });
const submissionSuccessful = computed(() => !!flash.value?.success);

const authUser = computed(() => usePage().props.auth.user as User);

// --- El "Cerebro" del Formulario ---
const form = useForm({
  folio_salida: props.nextFolioSalida,
  folio_interno: props.nextFolioInterno,
  destinatario: '',
  asunto: '',
  descripcion: '',
  prioridad: 'Ordinario' as 'Ordinario' | 'Urgente' | 'Extremadamente Urgente',
  status: 'Enviado',
  oficio_respuesta_id: null as number | null,
  area_ids: [null] as (number | null)[],
  asignaciones: [],
});

// --- Lógica para la Búsqueda de Oficio de Respuesta ---









// --- Lógica de Envío del Formulario ---
const submit = () => {
  form.post('/oficios/salida');
};


const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: 'Generar Oficio de Salida', href: '/oficios/salida/crear' },
];
</script>

<template>
  <Head title="Generar Oficio de Salida" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Generar Oficio de Salida</h1>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        



        <SuccessPanel 
            v-if="submissionSuccessful"
            title="¡Éxito!"
            :message="flash.success ?? ''"
        >
                <Link
                    href="/oficios/salida/crear"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    Generar Nuevo Oficio de Salida
                </Link>
        </SuccessPanel>

        <form v-else @submit.prevent="submit" class="space-y-6">

       <FormErrors :form="form" />


          <!-- SECCIÓN DE DATOS DEL OFICIO DE SALIDA -->
          <div class="border-b dark:border-gray-700 pb-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Datos del Oficio de Salida</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                      <label for="folio_salida" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio de Salida (Generado)</label>
                      <input type="text" id="folio_salida" v-model="form.folio_salida" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
                  </div>
                  <div>
                      <label for="folio_interno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Interno (Generado)</label>
                      <input type="text" id="folio_interno" v-model="form.folio_interno" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
                  </div>
                  
                  <OficioRespuestaSection :form="form" :searchable-oficios="props.searchableOficios" />
                  
                  

                  <div class="md:col-span-2">
                      <label for="destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destinatario (Opcional)</label>
                      <input type="text" id="destinatario" v-model="form.destinatario" class="mt-1 block w-full rounded-md shadow-sm" />
                      <div v-if="form.errors.destinatario" class="text-red-500 text-sm mt-1">{{ form.errors.destinatario }}</div>
                  </div>
                  <div class="md:col-span-2">
                      <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto (Opcional)</label>
                      <input type="text" id="asunto" v-model="form.asunto" class="mt-1 block w-full rounded-md shadow-sm" />
                      <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
                  </div>
                  <div class="md:col-span-2">
                      <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
                      <textarea id="descripcion" v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md shadow-sm"></textarea>
                  </div>
                  <div>
                      <label for="prioridad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad</label>
                      <select id="prioridad" v-model="form.prioridad" class="mt-1 block w-full rounded-md shadow-sm">
                          <option>Ordinario</option>
                          <option>Urgente</option>
                          <option>Extremadamente Urgente</option>
                      </select>
                  </div>
              </div>
          </div>

          <!-- SECCIÓN DE ÁREAS (condicional) -->
          <AreasSection 
            v-if="!form.oficio_respuesta_id" 
            :form="form" 
            :areas="props.areas" 
          />
          <!-- SECCIÓN DE ASIGNACIONES-->
          <AsignacionesSection 
          :form="form" 
          :users="props.users" 
          
          />



          <!-- Botones de Acción -->
          <div class="mt-6 flex justify-end space-x-4">
            <Link href="/oficios" class="px-4 py-2 rounded-md text-sm font-medium">Cancelar</Link>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md"
              :disabled="form.processing"
            >
              Generar Oficio
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
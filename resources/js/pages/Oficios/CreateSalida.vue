<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

// --- Definiciones de Tipos ---
interface Area { id: number; nombre: string; }
interface User { id: number; name: string; }
interface OficioEntrada { id: number; folio_externo: string | null; asunto: string; }

const props = defineProps<{
  areas: Area[];
  users: User[]; // Operativos
  oficiosDeEntrada: OficioEntrada[];
  nextFolioSalida: string;
  nextFolioInterno: string;
}>();

// --- Lógica de Éxito ---
const submissionSuccessful = ref(false);

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
  area_ids: [] as number[],
  asignaciones: [] as { user_id: number | null; permission: 'editor' | 'visualizador' }[],
});

// --- Lógica para la Búsqueda de Oficio de Respuesta ---
const searchTermOficioRespuesta = ref('');
const isOficioListVisible = ref(false);

const filteredOficiosDeEntrada = computed(() => {
    if (!searchTermOficioRespuesta.value) {
        return props.oficiosDeEntrada;
    }
    const search = searchTermOficioRespuesta.value.toLowerCase();
    return props.oficiosDeEntrada.filter(oficio => 
        (oficio.folio_externo && oficio.folio_externo.toLowerCase().includes(search)) ||
        (oficio.asunto && oficio.asunto.toLowerCase().includes(search))
    );
});

const selectOficioRespuesta = (oficio: OficioEntrada) => {
    form.oficio_respuesta_id = oficio.id;
    searchTermOficioRespuesta.value = `${oficio.folio_externo} - ${oficio.asunto}`;
    isOficioListVisible.value = false;
};

const clearOficioRespuesta = () => {
    form.oficio_respuesta_id = null;
    searchTermOficioRespuesta.value = '';
};


// --- Lógica para la sección de Áreas ---
const areaToAdd = ref<number | null>(null);
const availableAreas = computed(() => {
    return props.areas.filter(area => !form.area_ids.includes(area.id));
});
const selectedAreas = computed(() => {
    return props.areas.filter(area => form.area_ids.includes(area.id));
});
const addArea = () => {
    if (areaToAdd.value && !form.area_ids.includes(areaToAdd.value)) {
        form.area_ids.push(areaToAdd.value);
        areaToAdd.value = null;
    }
};
const removeArea = (areaId: number) => {
    form.area_ids = form.area_ids.filter((id: number) => id !== areaId);
};

// --- Lógica para la sección de Asignaciones ---
const addAsignacion = () => {
    if (props.users.length > 0) {
        form.asignaciones.push({ user_id: null, permission: 'visualizador' });
    }
}
const removeAsignacion = (index: number) => {
    form.asignaciones.splice(index, 1);
}

// --- Lógica de Envío del Formulario ---
const submit = () => {
  form.post('/oficios/salida', {
      onSuccess: () => {
          // Al tener éxito, mostramos la pantalla de éxito.
          submissionSuccessful.value = true;
      },
  });
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
        
        <div v-if="submissionSuccessful" class="text-center py-10">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium">¡Éxito!</h3>
            <p class="mt-1 text-sm">El oficio ha sido generado correctamente.</p>
            <div class="mt-6">
                <Link
                    href="/oficios/salida/crear"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    Generar Nuevo Oficio de Salida
                </Link>
            </div>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-6">
          
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
                  
                  <div class="md:col-span-2 relative">
                      <label for="oficio_respuesta_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Responder a Oficio de Entrada (Opcional)</label>
                      <div class="relative">
                          <input 
                            type="text"
                            id="oficio_respuesta_search"
                            v-model="searchTermOficioRespuesta"
                            placeholder="Buscar por folio externo o asunto..."
                            @focus="isOficioListVisible = true"
                            @blur="setTimeout(() => isOficioListVisible = false, 200)"
                            :disabled="!!form.oficio_respuesta_id"
                            class="mt-1 block w-full rounded-md shadow-sm"
                          />
                          <button 
                            v-if="form.oficio_respuesta_id" 
                            @click="clearOficioRespuesta"
                            type="button" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-red-500"
                          >
                            &times;
                          </button>
                      </div>
                      
                      <div v-if="isOficioListVisible && !form.oficio_respuesta_id" class="absolute z-10 w-full bg-white dark:bg-gray-900 border dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
                          <ul>
                              <li 
                                v-for="oficio in filteredOficiosDeEntrada" 
                                :key="oficio.id" 
                                @click="selectOficioRespuesta(oficio)"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                              >
                                  <span class="font-semibold">{{ oficio.folio_externo }}</span> - 
                                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ oficio.asunto }}</span>
                              </li>
                              <li v-if="filteredOficiosDeEntrada.length === 0" class="px-4 py-2 text-sm text-gray-500">
                                No se encontraron coincidencias.
                              </li>
                          </ul>
                      </div>
                      <div v-if="form.errors.oficio_respuesta_id" class="text-red-500 text-sm mt-1">{{ form.errors.oficio_respuesta_id }}</div>
                  </div>

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
          <div v-if="!form.oficio_respuesta_id" class="border-b dark:border-gray-700 pb-6">
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Áreas Involucradas</h2>
              <div v-if="selectedAreas.length > 0" class="mb-4 p-3 bg-gray-50 dark:bg-gray-900 rounded-md">
                  <ul class="flex flex-wrap gap-2">
                      <li v-for="area in selectedAreas" :key="area.id" class="flex items-center bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium px-2.5 py-1 rounded-full">
                          <span>{{ area.nombre }}</span>
                          <button type="button" @click="removeArea(area.id)" class="ml-2 text-blue-500 hover:text-blue-700">&times;</button>
                      </li>
                  </ul>
              </div>
              <div class="flex items-center space-x-4">
                  <select v-model="areaToAdd" class="block w-full rounded-md shadow-sm">
                      <option :value="null" disabled>Seleccione un área para añadir...</option>
                      <option v-for="area in availableAreas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                  </select>
                  <button type="button" @click="addArea" :disabled="!areaToAdd" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50">Añadir</button>
              </div>
              <div v-if="form.errors.area_ids" class="text-red-500 text-sm mt-2">{{ form.errors.area_ids }}</div>
          </div>

          <!-- SECCIÓN DE ASIGNACIONES -->
          <div>
              <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Asignar Permisos a Operativos (Opcional)</h2>
              <p v-if="users.length === 0" class="text-sm text-gray-500 dark:text-gray-400">No hay usuarios con rol 'operativo' disponibles para asignar.</p>
              
              <div v-else>
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
                <button type="button" @click="addAsignacion" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Añadir Asignación</button>
                <div v-if="form.errors.asignaciones" class="text-red-500 text-sm mt-1">{{ form.errors.asignaciones }}</div>
              </div>
          </div>

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
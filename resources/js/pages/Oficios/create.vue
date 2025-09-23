<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';

// Definición de tipos para las props que el controlador enviará
interface Expediente {
  id: number;
  numero_expediente: string;
  titulo: string;
}

interface Area {
    id: number;
    nombre: string;
}

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
  expedientes: Expediente[];
  areas: Area[];
  users: User[];
  nextFolioOficio: string;
  nextFolioInterno: string;
}>();

// Estado para controlar si se crea un nuevo expediente o se selecciona uno existente
const creandoNuevoExpediente = ref(true);

// Formulario de Inertia alineado con la nueva estructura de la BD y el controlador
const form = useForm({
  // Folios automáticos (vienen del controlador)
  folio_oficio: props.nextFolioOficio,
  folio_interno: props.nextFolioInterno,
  
  // Selección o creación de expediente
  expediente_id: null as number | null,
  nuevo_expediente_numero: '',
  nuevo_expediente_titulo: '',
  
  // Datos del Oficio
  tipo: 'entrada' as 'entrada' | 'salida',
  remitente: '',
  destinatario: '',
  asunto: '',
  descripcion: '',
  fecha_recepcion: new Date().toISOString().split('T')[0], // Fecha actual por defecto
  prioridad: 'Ordinario' as 'Ordinario' | 'Urgente' | 'Extremadamente Urgente',
  status: 'Pendiente',
  
  // Documentos
  documento_principal: null as File | null,
  anexos: [] as File[],

  // Relaciones
  area_ids: [] as number[],
  asignaciones: [] as { user_id: number; permission: 'editor' | 'visualizador' }[],
});

// Limpiar campos de expediente cuando se cambia de modo
watch(creandoNuevoExpediente, (newValue) => {
    if (newValue) {
        form.expediente_id = null;
    } else {
        form.nuevo_expediente_numero = '';
        form.nuevo_expediente_titulo = '';
    }
});

// Enviar formulario para crear un nuevo oficio
const submit = () => {
  // Se usa .post() porque permite el envío de archivos (multipart/form-data)
  form.post('/oficios', {
    onSuccess: () => {
      form.reset();
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
    form.asignaciones.push({ user_id: props.users[0]?.id, permission: 'visualizador' });
}

const removeAsignacion = (index: number) => {
    form.asignaciones.splice(index, 1);
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: 'Crear', href: '/oficios/create' },
];
</script>

<template>
  <Head title="Crear Oficio" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Registrar Nuevo Oficio</h1>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300">
        <form @submit.prevent="submit" class="space-y-6">

          <!-- SECCIÓN DE EXPEDIENTE -->
          <div class="border-b dark:border-gray-700 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Paso 1: Expediente</h2>
            <div class="flex items-center space-x-4 mb-4">
              <label class="flex items-center">
                <input type="radio" :value="true" v-model="creandoNuevoExpediente" class="form-radio h-4 w-4 text-blue-600">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Crear Nuevo Expediente</span>
              </label>
              <label class="flex items-center">
                <input type="radio" :value="false" v-model="creandoNuevoExpediente" class="form-radio h-4 w-4 text-blue-600">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Asociar a Expediente Existente</span>
              </label>
            </div>

            <div v-if="creandoNuevoExpediente" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="nuevo_expediente_numero" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Expediente Nuevo</label>
                <input type="text" id="nuevo_expediente_numero" v-model="form.nuevo_expediente_numero" class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.nuevo_expediente_numero" class="text-red-500 text-sm mt-1">{{ form.errors.nuevo_expediente_numero }}</div>
              </div>
              <div>
                <label for="nuevo_expediente_titulo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título del Expediente Nuevo</label>
                <input type="text" id="nuevo_expediente_titulo" v-model="form.nuevo_expediente_titulo" class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.nuevo_expediente_titulo" class="text-red-500 text-sm mt-1">{{ form.errors.nuevo_expediente_titulo }}</div>
              </div>
            </div>

            <div v-else>
              <label for="expediente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seleccionar Expediente</label>
              <select id="expediente_id" v-model="form.expediente_id" class="mt-1 block w-full rounded-md shadow-sm">
                <option :value="null" disabled>Elige un expediente</option>
                <option v-for="exp in expedientes" :key="exp.id" :value="exp.id">
                  {{ exp.numero_expediente }} - {{ exp.titulo }}
                </option>
              </select>
              <div v-if="form.errors.expediente_id" class="text-red-500 text-sm mt-1">{{ form.errors.expediente_id }}</div>
            </div>
            
             <div class="mt-6">
                <label for="area_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Áreas con Permiso al Expediente (Ctrl+Click para varios)</label>
                <select id="area_ids" v-model="form.area_ids" multiple class="mt-1 block w-full rounded-md shadow-sm">
                    <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                </select>
                <div v-if="form.errors.area_ids" class="text-red-500 text-sm mt-1">{{ form.errors.area_ids }}</div>
            </div>

          </div>

          <!-- SECCIÓN DE OFICIO -->
          <div class="border-b dark:border-gray-700 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Paso 2: Datos del Oficio</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="folio_oficio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio de Oficio (Generado)</label>
                <input type="text" id="folio_oficio" v-model="form.folio_oficio" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
              </div>
              <div>
                <label for="folio_interno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Interno (Generado)</label>
                <input type="text" id="folio_interno" v-model="form.folio_interno" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
              </div>

              <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Oficio</label>
                <select id="tipo" v-model="form.tipo" class="mt-1 block w-full rounded-md shadow-sm">
                  <option value="entrada">Entrada</option>
                  <option value="salida">Salida</option>
                </select>
              </div>

              <div v-if="form.tipo === 'entrada'">
                <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</label>
                <input type="text" id="remitente" v-model="form.remitente" class="mt-1 block w-full rounded-md shadow-sm" />
              </div>
              <div v-if="form.tipo === 'salida'">
                <label for="destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destinatario</label>
                <input type="text" id="destinatario" v-model="form.destinatario" class="mt-1 block w-full rounded-md shadow-sm" />
              </div>

              <div class="md:col-span-2">
                <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                <input type="text" id="asunto" v-model="form.asunto" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
              </div>
              <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
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

              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado Inicial</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md shadow-sm">
                  <option>Pendiente</option>
                  <option>En Proceso</option>
                  <option>Resuelto</option>
                </select>
              </div>
            </div>
          </div>

          <!-- SECCIÓN DE DOCUMENTOS -->
          <div class="border-b dark:border-gray-700 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Paso 3: Adjuntar Documentos</h2>
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

          <!-- SECCIÓN DE ASIGNACIONES -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Paso 4: Asignar Permisos (Opcional)</h2>
            <div v-for="(asignacion, index) in form.asignaciones" :key="index" class="flex items-center space-x-4 mb-2">
                <select v-model="asignacion.user_id" class="block w-1/2 rounded-md shadow-sm">
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
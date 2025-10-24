<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FormErrors from './Partials/FormErrors.vue';

// --- Se importan los tipos desde el archivo central ---
import { type BreadcrumbItem, type Oficio } from '@/types';

const props = defineProps<{
  oficio: Oficio;
}>();

// --- El "Cerebro" del Formulario ---
// Se inicializa con los datos del oficio que se está editando
const form = useForm({
    _method: 'PUT',
    asunto: props.oficio.asunto,
    descripcion: props.oficio.descripcion || '',
    prioridad: props.oficio.prioridad,
    status: props.oficio.status,
    resolucion: props.oficio.resolucion || '',
    // 'oficio_respuesta_id' se podría añadir aquí si se desea editar la relación
});

// Función para enviar el formulario a la ruta de actualización
const submit = () => {
    form.post(`/oficios/${props.oficio.id}`);
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: 'Detalle', href: `/oficios/${props.oficio.id}` },
  { title: 'Editar' },
];
</script>

<template>
    <Head :title="`Editar: ${oficio.asunto}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
                Editar Oficio: {{ oficio.folio_externo || oficio.folio_salida }}
            </h1>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 transition-colors duration-300 max-w-3xl mx-auto">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <FormErrors :form="form" />

                    <!-- Campo: Asunto -->
                    <div class="md:col-span-2">
                        <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                        <input
                            type="text"
                            id="asunto"
                            v-model="form.asunto"
                            required
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Campo: Descripción -->
                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
                        <textarea
                            id="descripcion"
                            v-model="form.descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campo: Prioridad -->
                        <div>
                            <label for="prioridad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad</label>
                            <select id="prioridad" v-model="form.prioridad" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                                <option>Ordinario</option>
                                <option>Urgente</option>
                                <option>Extremadamente Urgente</option>
                            </select>
                        </div>

                        <!-- Campo: Estado -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                                <option>Pendiente</option>
                                <option>En Proceso</option>
                                <option>Resuelto</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Campo: Resolución -->
                    <div class="md:col-span-2">
                        <label for="resolucion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolución / Nota de Cierre (Opcional)</label>
                        <textarea
                            id="resolucion"
                            v-model="form.resolucion"
                            rows="3"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"
                            placeholder="Añada una nota si el estado es 'Resuelto'..."
                        ></textarea>
                    </div>


                    <!-- Botones de acción -->
                    <div class="flex items-center justify-end space-x-4">
                        <Link :href="`/oficios/${oficio.id}`" class="px-4 py-2 rounded-md text-sm font-medium">
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md"
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


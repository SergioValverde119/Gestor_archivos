<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

// Define los tipos de datos que este componente espera recibir
interface Oficio {
  id: number;
  asunto: string;
  folio_externo: string | null;
  folio_salida: string | null;
  status: string;
  prioridad: string;
  expediente: {
    id: number;
    numero_expediente: string;
  } | null;
}

const props = defineProps<{
  oficio: Oficio;
}>();

// Propiedades computadas para manejar los estilos de las etiquetas
const statusClasses = computed(() => {
    if (!props.oficio.status) return 'bg-gray-100 text-gray-800';
    switch (props.oficio.status.toLowerCase()) {
        case 'pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'en proceso': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'resuelto': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
});

const priorityClasses = computed(() => {
    if (!props.oficio.prioridad) return 'bg-gray-100 text-gray-800';
    switch (props.oficio.prioridad.toLowerCase()) {
        case 'urgente': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'extremadamente urgente': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
});

// Determina qué folio mostrar (el externo o el de salida)
const folioPrincipal = computed(() => props.oficio.folio_externo || props.oficio.folio_salida || 'N/A');

</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
            <!-- Lado izquierdo: Título y expediente -->
            <div class="flex-grow">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ oficio.asunto }}</h1>
                <p v-if="oficio.expediente" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Expediente: 
                    <Link :href="`/expedientes/${oficio.expediente.id}`" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        {{ oficio.expediente.numero_expediente }}
                    </Link>
                </p>
            </div>
            <!-- Lado derecho: Folio y etiquetas de estado -->
            <div class="flex-shrink-0 flex flex-col items-end">
                 <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Folio: {{ folioPrincipal }}</p>
                 <div class="mt-2 flex items-center gap-x-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full" :class="statusClasses">
                        {{ oficio.status }}
                    </span>
                     <span class="px-3 py-1 text-xs font-semibold rounded-full" :class="priorityClasses">
                        {{ oficio.prioridad }}
                    </span>
                 </div>
            </div>
        </div>
    </div>
</template>
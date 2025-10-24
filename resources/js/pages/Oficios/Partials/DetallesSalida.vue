<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { type Oficio } from '@/types';


const props = defineProps<{
  oficio: Oficio;
}>();

// Función para formatear fechas
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const userTimezoneOffset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() + userTimezoneOffset).toLocaleDateString('es-MX', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-4">Detalles de Salida</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div class="md:col-span-1">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Destinatario:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ oficio.destinatario || 'No especificado' }}</dd>
            </div>
            <div class="md:col-span-1">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Generado Por:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ oficio.recibidoPor?.name || 'Sistema' }}</dd>
            </div>
            <div class="md:col-span-1">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Fecha Límite de Respuesta:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ formatDate(oficio.fecha_limite) }}</dd>
            </div>
            <div v-if="oficio.respuestaA" class="md:col-span-1">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Responde al Oficio:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">
                    <Link :href="`/oficios/${oficio.respuestaA.id}`" class="text-blue-600 hover:underline">
                        Folio Interno {{ oficio.respuestaA.folio_interno }}
                    </Link>
                </dd>
            </div>
        </dl>
    </div>
</template>


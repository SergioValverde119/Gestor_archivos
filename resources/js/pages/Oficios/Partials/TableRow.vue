<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowRight, ArrowLeft } from 'lucide-vue-next';
import OficioActionButtons from './OficioActionButtons.vue';
import { type Oficio } from '@/types'; // Importa el tipo Oficio completo

const props = defineProps<{
  oficio: Oficio;
  visibleHeaders: { key: string; label: string; }[];
}>();

// --- Funciones de Formateo y Estilos ---
const formatDate = (dateString: string | null): string => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  // Ajuste para asegurar que se muestre la fecha local correcta
  const userTimezoneOffset = date.getTimezoneOffset() * 60000;
  return new Date(date.getTime() + userTimezoneOffset).toLocaleDateString('es-MX', {
    year: 'numeric', month: '2-digit', day: '2-digit'
  });
};

const statusClasses = (status: string) => {
    if (!status) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    switch (status.toLowerCase()) {
        case 'pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'en proceso': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'resuelto': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

const priorityClasses = (priority: string) => {
    if (!priority) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    switch (priority.toLowerCase()) {
        case 'urgente': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'extremadamente urgente': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

// Calcula el número de anexos
const anexoCount = computed(() => {
    if (!props.oficio.documentos) return 0;
    return props.oficio.documentos.filter(d => d.rol_documento === 'anexo').length;
});
</script>

<template>
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
        <td v-for="col in visibleHeaders" :key="col.key" class="px-6 py-4 whitespace-nowrap text-sm">
            <template v-if="col.key === 'acciones'"><OficioActionButtons :oficio="oficio" /></template>
            <template v-else-if="col.key === 'tipo'"><span v-if="oficio.tipo === 'entrada'" class="inline-flex items-center text-blue-600"><ArrowRight class="w-4 h-4 mr-1"/> Entrada</span><span v-else class="inline-flex items-center text-green-600"><ArrowLeft class="w-4 h-4 mr-1"/> Salida</span></template>
            <template v-else-if="col.key === 'folio'">{{ oficio.folio_externo || oficio.folio_salida || 'N/A' }}</template>
            <template v-else-if="col.key === 'asunto'">{{ oficio.asunto }}</template>
            <template v-else-if="col.key === 'status'"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClasses(oficio.status)">{{ oficio.status }}</span></template>
            <template v-else-if="col.key === 'prioridad'"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="priorityClasses(oficio.prioridad)">{{ oficio.prioridad }}</span></template>
            <template v-else-if="col.key === 'fechaRegistro'">{{ formatDate(oficio.created_at) }}</template>
            <template v-else-if="col.key === 'folioInterno'">{{ oficio.folio_interno || 'N/A' }}</template>
            <template v-else-if="col.key === 'expediente'"><Link v-if="oficio.expediente" :href="`/expedientes/${oficio.expediente.id}`" class="text-blue-600 hover:underline">{{ oficio.expediente.numero_expediente }}</Link><span v-else>N/A</span></template>
            <template v-else-if="col.key === 'remitente_destinatario'">{{ oficio.remitente || oficio.destinatario || 'N/A' }}</template>
            <template v-else-if="col.key === 'descripcion'">{{ oficio.descripcion?.substring(0, 30) }}...</template>
            <template v-else-if="col.key === 'fechaRecepcion'">{{ formatDate(oficio.fecha_recepcion) }}</template>
            <template v-else-if="col.key === 'fechaLimite'">{{ formatDate(oficio.fecha_limite) }}</template>
            <template v-else-if="col.key === 'registradoPor'">{{ oficio.recibidoPor?.name || 'Sistema' }}</template>
            <template v-else-if="col.key === 'areas'">{{ oficio.expediente?.areas.map(a => a.nombre).join(', ') || 'N/A' }}</template>
            <template v-else-if="col.key === 'asignadoA'">{{ oficio.permissions.map(p => p.user.name).join(', ') || 'N/A' }}</template>
            <template v-else-if="col.key === 'respondeA'"><Link v-if="oficio.respuestaA" :href="`/oficios/${oficio.respuestaA.id}`" class="text-blue-600 hover:underline">{{ oficio.respuestaA.folio_interno }}</Link><span v-else>-</span></template>
            <template v-else-if="col.key === 'turnoDGAF'"><span v-if="oficio.tiene_turno_dgaf" class="text-green-500 font-bold">Sí</span><span v-else>No</span></template>
            <template v-else-if="col.key === 'anexos'">{{ anexoCount }}</template>
        </td>
    </tr>
</template>
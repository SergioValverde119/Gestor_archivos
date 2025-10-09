<script setup lang="ts">
// --- Definición de Tipos ---
interface User {
    id: number;
    name: string;
}

interface Oficio {
  remitente: string | null;
  fecha_recepcion: string | null;
  recibidoPor: User | null;
  tiene_turno_dgaf: boolean;
  folio_turno_dgaf: string | null;
  fecha_turno_dgaf: string | null;
}

const props = defineProps<{
  oficio: Oficio;
}>();

// Función para formatear fechas
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    // Ajuste para asegurar que se muestre la fecha local correcta
    const userTimezoneOffset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() + userTimezoneOffset).toLocaleDateString('es-MX', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-4">Detalles de Entrada</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div class="col-span-2">
                <dt class="font-medium text-gray-500 dark:text-gray-400">Remitente:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ oficio.remitente || 'No especificado' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Fecha de Recepción:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ formatDate(oficio.fecha_recepcion) }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Registrado Por:</dt>
                <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ oficio.recibidoPor?.name || 'N/A' }}</dd>
            </div>

            <!-- Sección del Turno DGAF (solo si existe) -->
            <template v-if="oficio.tiene_turno_dgaf">
                <div class="col-span-2 border-t dark:border-gray-700 pt-4 mt-2">
                    <h4 class="font-semibold text-gray-600 dark:text-gray-300">Turno D.G.A.F.</h4>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Folio del Turno:</dt>
                    <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ oficio.folio_turno_dgaf || 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Fecha del Turno:</dt>
                    <dd class="text-gray-900 dark:text-gray-100 mt-1">{{ formatDate(oficio.fecha_turno_dgaf) }}</dd>
                </div>
            </template>
        </dl>
    </div>
</template>
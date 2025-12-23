<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowLeft, FileText } from 'lucide-vue-next';
import { type ExpedienteConOficios } from '@/types';

const props = defineProps<{
  expediente: ExpedienteConOficios | null;
  oficioActualId: number;
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
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Historial del Expediente</h3>
        <div v-if="expediente && expediente.oficios.length > 0">
            <div class="border-l-2 border-gray-200 dark:border-gray-700 ml-2">
                <div v-for="oficio in expediente.oficios" :key="oficio.id" class="relative mb-6">
                    <!--<pre class="bg-red-200 text-black p-2 rounded">{{ oficio }}</pre>-->
                    <div class="absolute -left-[11px] h-5 w-5 rounded-full" 
                         :class="oficio.tipo === 'entrada' ? 'bg-blue-500' : 'bg-green-500'">
                    </div>
                    <div class="pl-8">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold" :class="oficio.tipo === 'entrada' ? 'text-blue-600 dark:text-blue-400' : 'text-green-600 dark:text-green-400'">
                                <ArrowRight v-if="oficio.tipo === 'entrada'" class="w-4 h-4 inline-block mr-1" />
                                <ArrowLeft v-else class="w-4 h-4 inline-block mr-1" />
                                Oficio de {{ oficio.tipo }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(oficio.created_at) }}</span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ oficio.asunto }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                          <span> Folio: {{ oficio.folio_externo || oficio.folio_salida }} <span v-if="oficio.recibido_por"> | Recibido por: {{ oficio.recibido_por.name }}</span></span>
                           <Link v-if="oficio.id !== oficioActualId" :href="`/oficios/${oficio.id}`" class="text-blue-600 hover:underline flex items-center">
                                Ver <FileText class="w-3 h-3 ml-1" />
                           </Link>
                           <span v-else class="font-semibold text-indigo-500">Estás aquí</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p v-else class="text-sm text-gray-500 dark:text-gray-400">Este expediente no tiene más oficios.</p>
    </div> 
</template>

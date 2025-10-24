<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// --- CORRECCIÓN: Se importan los tipos desde el archivo central ---
import { type BreadcrumbItem, type Oficio } from '@/types';

// Importa todos los componentes finalizados
import OficioHeader from './Partials/OficioHeader.vue';
import DetallesEntrada from './Partials/DetallesEntrada.vue';
import DetallesSalida from './Partials/DetallesSalida.vue';
import ListaDocumentos from './Partials/ListaDocumentos.vue';
import AsignacionesCard from './Partials/AsignacionesCard.vue';
import HistorialExpediente from './Partials/HistorialExpediente.vue';

// --- (Las definiciones locales de tipos se han eliminado) ---

const props = defineProps<{
  oficio: Oficio;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: props.oficio.expediente?.numero_expediente || 'Detalle', href: `/expedientes/${props.oficio.expediente?.id}` },
  { title: props.oficio.asunto ?? 'Detalle del Oficio' },
];

</script>

<template>
    <Head :title="`Oficio: ${oficio.asunto}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="space-y-8">
                
                <OficioHeader :oficio="oficio" />

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <DetallesEntrada v-if="oficio.tipo === 'entrada'" :oficio="oficio" />
                        <DetallesSalida v-if="oficio.tipo === 'salida'" :oficio="oficio" />
                        
                        <!-- Sección de Descripción -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Descripción</h3>
                            <p v-if="oficio.descripcion" class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ oficio.descripcion }}</p>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400">No se proporcionó una descripción.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                         <ListaDocumentos :oficio="oficio" />
                         <AsignacionesCard :oficio="oficio" />
                    </div>
                </div>

                <!-- Historial del Expediente -->
                <HistorialExpediente :expediente="oficio.expediente" :oficio-actual-id="oficio.id" />
                
            </div>
        </div>
    </AppLayout>
</template>
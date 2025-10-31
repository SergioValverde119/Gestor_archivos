<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type ExpedienteConOficios } from '@/types';
import { Briefcase, Building, FileText } from 'lucide-vue-next';

// --- ¡REUTILIZAMOS EL COMPONENTE! ---
// (Ajusta la ruta si es necesario, pero probablemente esté en los partials de Oficios)
import HistorialExpediente from '../Oficios/Partials/HistorialExpediente.vue'; 

// --- Definición de Props ---
const props = defineProps<{
    expediente: ExpedienteConOficios;
}>();

// --- Breadcrumbs para el Layout ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Expedientes', href: '/expedientes' },
    { title: props.expediente.numero_expediente || 'Detalle' },
];

// Función simple para formatear las áreas
const formatAreas = (areas: { nombre: string }[]) => {
    if (!areas || areas.length === 0) return 'Sin áreas asignadas';
    return areas.map(a => a.nombre).join(', ');
};
</script>

<template>
    <Head :title="`Expediente: ${expediente.numero_expediente}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="space-y-8">

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    
                    <div class="mb-4">
                        <div class="flex items-center space-x-2">
                            <Briefcase class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ expediente.titulo }}
                            </h2>
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Núm. Expediente: {{ expediente.numero_expediente }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                <Building class="w-4 h-4 mr-2" />
                                Áreas Involucradas
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 pl-6">
                                {{ formatAreas(expediente.areas) }}
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                <FileText class="w-4 h-4 mr-2" />
                                Descripción
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap pl-6">
                                {{ expediente.descripcion || 'No se proporcionó una descripción.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <HistorialExpediente 
                    :expediente="expediente" 
                    :oficio-actual-id="0"
                />
                
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Oficio, type Area, type User, type Expediente } from '@/types';
import EditEntradaForm from './Partials/EditEntradaForm.vue';
import EditSalidaForm from './Partials/EditSalidaForm.vue';

// --- WAYFINDER ---
import * as oficioRoutes from '@/routes/oficios';

const props = defineProps<{
    oficio: Oficio;
    areas: Area[];
    users: User[]; // Para asignar responsables
    expedientes: Expediente[]; // Para vincular a carpeta
    oficios_posibles_respuesta?: Oficio[]; // Solo para Salida (a cuál responde)
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Oficios', href: oficioRoutes.index() as unknown as string },
    { title: `Editar ${props.oficio.tipo === 'entrada' ? 'Entrada' : 'Salida'}`, href: '#' },
];
</script>

<template>
    <Head :title="`Editar Oficio ${oficio.folio_interno}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto p-6">
            
            <EditEntradaForm 
                v-if="oficio.tipo === 'entrada'" 
                :oficio="oficio"
                :areas="areas"
                :users="users"
                :expedientes="expedientes"
            />

            <EditSalidaForm 
                v-else 
                :oficio="oficio"
                :areas="areas"
                :users="users"
                :expedientes="expedientes"
                :oficios-posibles="oficios_posibles_respuesta"
            />

        </div>
    </AppLayout>
</template>
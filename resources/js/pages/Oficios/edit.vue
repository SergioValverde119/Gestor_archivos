<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Oficio, type Area, type User, type Expediente } from '@/types';
import { Head } from '@inertiajs/vue3';
import EditEntradaForm from './Partials/EditEntradaForm.vue';
import EditSalidaForm from './Partials/EditSalidaForm.vue';

const props = defineProps<{
    oficio: Oficio;
    areas: Area[];
    users: User[];
    expedientes: Expediente[];
    // Este prop es opcional porque solo viene cuando editamos una Salida
    oficios_posibles_respuesta?: Oficio[]; 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Oficios', href: '/oficios' },
    { title: 'Editar Oficio', href: `/oficios/${props.oficio.id}/edit` },
];
</script>

<template>
    <Head title="Editar Oficio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
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
                    :oficiosPosibles="oficios_posibles_respuesta || []" 
                />
                </div>
        </div>
    </AppLayout>
</template>
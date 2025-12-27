<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'; // <-- Esto trae la Barra Lateral y Superior
import { type BreadcrumbItem } from '@/types';

// Definimos las "migas de pan" para que la barra superior sepa dónde estamos
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inicio',
        href: '/dashboard', // O la ruta que prefieras
    },
];
</script>

<template>
    <Head title="Inicio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <div class="p-6">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-4">
                Bienvenido, {{ $page.props.auth.user.name }}
            </h1>

            <div class="border-2 border-dashed border-gray-300 rounded-lg h-96 flex items-center justify-center text-gray-400">
                Aquí construirás tus tarjetas de oficios...
            </div>

        </div>

    </AppLayout>
</template>
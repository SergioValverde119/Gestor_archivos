<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { index, store } from '@/routes/areas'; 
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    areas: { id: number; nombre: string }[];
}>();

const form = useForm({
    nombre: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Áreas',
        href: index().url, // Sintaxis de Wayfinder para la URL
    },
];

function addArea() {
    form.post(store().url, { // Sintaxis de Wayfinder para el envío del formulario
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Áreas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Áreas</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Añadir una nueva área</h2>

                    <form @submit.prevent="addArea" class="flex items-end gap-4 mb-8">
                        <div class="grid gap-2 flex-grow">
                            <Label for="nombre">Nombre del área</Label>
                            <Input id="nombre" type="text" v-model="form.nombre" required />
                            <InputError :message="form.errors.nombre" />
                        </div>
                        <Button :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Añadir Área
                        </Button>
                    </form>

                    <h2 class="text-xl font-bold mb-4">Listado de Áreas</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="area in areas" :key="area.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ area.nombre }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
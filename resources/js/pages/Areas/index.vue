<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { index, store, destroy } from '@/routes/areas'; 
import { LoaderCircle, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    areas: { id: number; nombre: string }[];
}>();

const form = useForm({
    nombre: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Áreas',
        href: index().url,
    },
];

const areaToDelete = ref<{ id: number; nombre: string } | null>(null);

function addArea() {
    form.post(store().url, {
        onSuccess: () => form.reset(),
    });
}

function confirmDeletion(area: { id: number; nombre: string }) {
    if (confirm(`¿Estás seguro de que quieres eliminar el área "${area.nombre}"?`)) {
        deleteArea(area.id);
    }
}

function deleteArea(areaId: number) {
    router.delete(destroy(areaId).url);
}
</script>

<template>
    <Head title="Áreas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gestión de Áreas</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Contenedor principal del formulario y la tabla -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Añadir una nueva área</h2>

                    <form @submit.prevent="addArea" class="flex items-end gap-4 mb-8">
                        <div class="grid gap-2 flex-grow">
                            <Label for="nombre" class="text-gray-700 dark:text-gray-300">Nombre del área</Label>
                            <Input id="nombre" type="text" v-model="form.nombre" required />
                            <InputError :message="form.errors.nombre" />
                        </div>
                        <Button :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Añadir Área
                        </Button>
                    </form>

                    <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Listado de Áreas</h2>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="area in areas" :key="area.id">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ area.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Button
                                        @click="confirmDeletion(area)"
                                        variant="ghost"
                                        class="hover:bg-red-500 hover:text-white dark:hover:bg-red-600 dark:hover:text-white transition-colors duration-200"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
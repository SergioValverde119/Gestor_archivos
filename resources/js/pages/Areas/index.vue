<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { LoaderCircle, Trash2, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    areas: { id: number; nombre: string }[];
}>();

// Formulario para crear una nueva área
const createForm = useForm({
    nombre: '',
});

// Referencia para la ID del área que se está editando
const editingAreaId = ref<number | null>(null);

// Formulario para editar un área existente
const editForm = useForm({
    nombre: '',
});

// Referencia para el área que se desea eliminar
const areaToDelete = ref<{ id: number; nombre: string } | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Áreas',
        href: '/areas', // Usar un enlace directo para simplificar
    },
];

// Función para guardar una nueva área
const storeArea = () => {
    createForm.post('/areas', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
        },
    });
};

// Función para iniciar la edición de un área
const startEditing = (area: { id: number; nombre: string }) => {
    editingAreaId.value = area.id;
    editForm.nombre = area.nombre;
};

// Función para cancelar la edición
const cancelEditing = () => {
    editingAreaId.value = null;
    editForm.reset();
};

// Función para actualizar un área
const updateArea = (areaId: number) => {
    editForm.put(`/areas/${areaId}`, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEditing();
        },
    });
};

// Función para confirmar la eliminación de un área
const confirmDeletion = (area: { id: number; nombre: string }) => {
    areaToDelete.value = area;
};

// Función para eliminar un área
const deleteArea = () => {
    if (areaToDelete.value) {
        router.delete(`/areas/${areaToDelete.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                areaToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Áreas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12 px-4 md:px-6">
            <div class="max-w-4xl mx-auto">
                <!-- Sección para crear una nueva área -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Crear Nueva Área</h2>
                    <form @submit.prevent="storeArea" class="flex flex-col sm:flex-row items-end gap-4">
                        <div class="flex-1 w-full">
                            <Label for="nombre" class="sr-only">Nombre del Área</Label>
                            <Input
                                id="nombre"
                                type="text"
                                class="w-full"
                                v-model="createForm.nombre"
                                placeholder="Nombre del Área"
                                autocomplete="off"
                            />
                            <InputError :message="createForm.errors.nombre" class="mt-2" />
                        </div>
                        <Button
                            type="submit"
                            :disabled="createForm.processing || !createForm.nombre"
                            class="w-full sm:w-auto"
                        >
                            <LoaderCircle v-if="createForm.processing" class="animate-spin mr-2" />
                            Crear
                        </Button>
                    </form>
                </div>

                <!-- Sección para la lista de áreas -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Lista de Áreas</h2>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="area in props.areas" :key="area.id" class="py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <!-- Modo de edición -->
                            <div v-if="editingAreaId === area.id" class="flex-1 w-full flex items-center gap-2">
                                <Input
                                    v-model="editForm.nombre"
                                    class="flex-1"
                                    :class="{ 'border-red-500': editForm.errors.nombre }"
                                    @keyup.enter="updateArea(area.id)"
                                    @keyup.esc="cancelEditing"
                                    autocomplete="off"
                                />
                                <InputError :message="editForm.errors.nombre" class="mt-2" />
                                <div class="flex gap-2">
                                    <Button
                                        size="sm"
                                        @click="updateArea(area.id)"
                                        :disabled="editForm.processing"
                                        variant="outline"
                                    >
                                        <LoaderCircle v-if="editForm.processing" class="animate-spin mr-2" />
                                        Guardar
                                    </Button>
                                    <Button size="sm" @click="cancelEditing" variant="ghost">
                                        Cancelar
                                    </Button>
                                </div>
                            </div>
                            <!-- Modo de visualización -->
                            <div v-else class="flex-1 w-full text-gray-900 dark:text-gray-100">
                                {{ area.nombre }}
                            </div>

                            <!-- Botones de acción -->
                            <div v-if="editingAreaId !== area.id" class="flex gap-2">
                                <Button size="sm" @click="startEditing(area)" variant="secondary">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button size="sm" @click="confirmDeletion(area)" variant="destructive">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </li>
                    </ul>

                    <p v-if="props.areas.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
                        No hay áreas registradas.
                    </p>
                </div>
            </div>
        </div>

        <!-- Diálogo de confirmación para eliminar -->
        <div v-if="areaToDelete" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-sm">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Confirmar Eliminación</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    ¿Estás seguro de que deseas eliminar el área "{{ areaToDelete.nombre }}"? Esta acción no se puede deshacer.
                </p>
                <div class="flex justify-end space-x-2">
                    <Button @click="areaToDelete = null" variant="secondary">Cancelar</Button>
                    <Button @click="deleteArea" variant="destructive">Eliminar</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
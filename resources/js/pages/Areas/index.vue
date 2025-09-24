<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { LoaderCircle, Trash2, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

// --- Definición de tipos para la paginación ---
interface Area {
    id: number;
    nombre: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedAreas {
  data: Area[];
  links: PaginationLink[];
}

const props = defineProps<{
    areas: PaginatedAreas;
}>();

// --- Objeto con las rutas escritas a mano (solución temporal) ---
const referencias = {
    areas: {
        index: () => '/areas',
        store: () => '/areas',
        update: (id: number) => `/areas/${id}`,
        destroy: (id: number) => `/areas/${id}`,
    }
};

const createForm = useForm({
    nombre: '',
});

const editingAreaId = ref<number | null>(null);

const editForm = useForm({
    nombre: '',
});

const areaToDelete = ref<Area | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Áreas',
        href: referencias.areas.index(), 
    },
];

const storeArea = () => {
    createForm.post(referencias.areas.store(), {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const startEditing = (area: Area) => {
    editingAreaId.value = area.id;
    editForm.nombre = area.nombre;
};

const cancelEditing = () => {
    editingAreaId.value = null;
    editForm.reset();
};

const updateArea = (areaId: number) => {
    editForm.put(referencias.areas.update(areaId), {
        preserveScroll: true,
        onSuccess: () => cancelEditing(),
    });
};

const confirmDeletion = (area: Area) => {
    areaToDelete.value = area;
};

const deleteArea = () => {
    if (areaToDelete.value) {
        router.delete(referencias.areas.destroy(areaToDelete.value.id), {
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
                            <LoaderCircle v-if="createForm.processing" class="animate-spin mr-2 h-4 w-4" />
                            Crear
                        </Button>
                    </form>
                </div>

                <!-- Sección para la lista de áreas -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Lista de Áreas</h2>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Iterar sobre props.areas.data -->
                        <li v-for="area in props.areas.data" :key="area.id" class="py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
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
                                        <LoaderCircle v-if="editForm.processing" class="animate-spin mr-2 h-4 w-4" />
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

                    <p v-if="props.areas.data.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
                        No hay áreas registradas.
                    </p>

                    <!-- Controles de Paginación -->
                    <div v-if="props.areas.links.length > 3" class="flex justify-center mt-6">
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <Link
                                v-for="(link, key) in props.areas.links"
                                :key="key"
                                :href="link.url ?? '#'"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                :class="{
                                    'bg-blue-500 text-white dark:bg-blue-600 dark:text-white border-blue-500 dark:border-blue-600': link.active,
                                    'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active && link.url,
                                    'opacity-50 pointer-events-none bg-gray-100 dark:bg-gray-900': !link.url,
                                }"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
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
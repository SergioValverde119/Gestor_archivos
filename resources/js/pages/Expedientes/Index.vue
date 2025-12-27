<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Search, Plus, FolderOpen, FileText, Eye, Edit } from 'lucide-vue-next';
import { type BreadcrumbItem, type Expediente, type Pagination } from '@/types';

// --- Wayfinder ---
import * as expedienteRoutes from '@/routes/expedientes'; 

const props = defineProps<{
    expedientes: {
        data: Expediente[];
        links: Pagination[];
        meta: any;
    };
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    // 🔧 FIX 1: Forzamos 'as string' para calmar a TypeScript con Wayfinder
    { title: 'Expedientes', href: expedienteRoutes.index() as unknown as string }, 
];

// Lógica de Búsqueda
const search = ref(props.filters.search || '');
let timeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            expedienteRoutes.index() as unknown as string, 
            { search: value }, 
            { preserveState: true, replace: true }
        );
    }, 300);
});

// Helpers visuales
const formatAreas = (areas: any[]) => {
    if (!areas || areas.length === 0) return '—';
    return areas.map(a => a.nombre).join(', ');
};

// 🔧 FIX 2: Eliminamos la función getStatusColor porque no existe el campo status
</script>

<template>
    <Head title="Expedientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Expedientes</h1>
                    <p class="text-gray-500 text-sm mt-1">Administra y consulta las carpetas digitales.</p>
                </div>
                
                <Link 
                    :href="expedienteRoutes.create()" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    Nuevo Expediente
                </Link>
            </div>

            <div class="mb-6">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <Search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" 
                        placeholder="Buscar por número, título..." 
                    />
                </div>
            </div>

            <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">No. Expediente</th>
                            <th scope="col" class="px-6 py-3">Asunto / Título</th>
                            <th scope="col" class="px-6 py-3">Áreas Asignadas</th>
                            <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="expediente in expedientes.data" :key="expediente.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <div class="flex items-center">
                                    <FolderOpen class="w-4 h-4 mr-2 text-yellow-500" />
                                    {{ expediente.numero_expediente }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ expediente.titulo }}</div>
                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ expediente.descripcion }}</div>
                            </td>

                            <td class="px-6 py-4">
                                {{ formatAreas(expediente.areas) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <Link 
                                        :href="expedienteRoutes.show({ expediente: expediente.id })" 
                                        class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400" 
                                        title="Ver Detalle"
                                    >
                                        <Eye class="w-5 h-5" />
                                    </Link>
                                    
                                    <Link 
                                        :href="expedienteRoutes.edit({ expediente: expediente.id })" 
                                        class="text-yellow-600 hover:text-yellow-900 dark:hover:text-yellow-400" 
                                        title="Editar"
                                    >
                                        <Edit class="w-5 h-5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-if="expedientes.data.length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                <FileText class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                                No se encontraron expedientes.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end" v-if="expedientes.links && expedientes.links.length > 3">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <Link 
                        v-for="(link, k) in expedientes.links" 
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                            link.active 
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                            k === 0 ? 'rounded-l-md' : '',
                            k === expedientes.links.length - 1 ? 'rounded-r-md' : ''
                        ]"
                    />
                </nav>
            </div>

        </div>
    </AppLayout>
</template>
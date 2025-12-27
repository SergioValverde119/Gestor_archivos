<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Expediente, type Area } from '@/types';
import { Save, X, FilePenLine, Building2, AlignLeft, FileType } from 'lucide-vue-next';

// --- IMPORTACIÓN DE WAYFINDER ---
import * as expedienteRoutes from '@/routes/expedientes';

// Props
const props = defineProps<{
    expediente: Expediente;
    areas: Area[];
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { 
        title: 'Expedientes', 
        href: expedienteRoutes.index() as unknown as string 
    },
    { title: 'Editar', href: '#' },
];

// Formulario
const form = useForm({
    numero_expediente: props.expediente.numero_expediente,
    titulo: props.expediente.titulo,
    descripcion: props.expediente.descripcion,
    area_ids: props.expediente.areas?.map(a => a.id) || [],
});

const submit = () => {
    form.put(expedienteRoutes.update({ expediente: props.expediente.id }) as unknown as string, {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Editar Expediente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-6">
            
            <form @submit.prevent="submit">
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                    
                    <div class="relative bg-gradient-to-r from-indigo-600 to-blue-500 px-8 py-6">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                                    <FilePenLine class="w-6 h-6 text-indigo-100" />
                                    Editar Expediente
                                </h2>
                                <p class="mt-1 text-indigo-100 text-sm opacity-90">
                                    Actualizando la información de: <span class="font-semibold text-white">"{{ expediente.titulo }}"</span>
                                </p>
                            </div>
                            <FileType class="w-24 h-24 text-white opacity-10 absolute -right-6 -bottom-10 rotate-12" />
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-12 gap-8">
                        
                        <div class="md:col-span-8 space-y-6">
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Título del Asunto
                                </label>
                                <div class="relative">
                                    <input 
                                        v-model="form.titulo"
                                        type="text"
                                        class="w-full pl-4 pr-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-medium transition-all"
                                        placeholder="Ej. Solicitud de Licencia de Construcción..."
                                    />
                                </div>
                                <p v-if="form.errors.titulo" class="mt-1 text-sm text-red-600 font-medium">{{ form.errors.titulo }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                        Folio / Número
                                    </label>
                                    <input 
                                        v-model="form.numero_expediente"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 dark:bg-gray-800"
                                        placeholder="Ej. EXP-2025/001"
                                    />
                                    <p v-if="form.errors.numero_expediente" class="mt-1 text-sm text-red-600">{{ form.errors.numero_expediente }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                                    <AlignLeft class="w-4 h-4 text-gray-400"/>
                                    Descripción y Notas
                                </label>
                                <textarea 
                                    v-model="form.descripcion"
                                    rows="5"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                    placeholder="Detalles adicionales sobre este expediente..."
                                ></textarea>
                                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-4 space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                                <label class="block text-sm font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    <Building2 class="w-4 h-4 text-indigo-500"/>
                                    Áreas Asignadas
                                </label>
                                
                                <div class="relative">
                                    <select 
                                        v-model="form.area_ids" 
                                        multiple
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-64 text-sm"
                                    >
                                        <option v-for="area in areas" :key="area.id" :value="area.id" class="p-2 border-b border-gray-100 dark:border-gray-600 last:border-0 hover:bg-indigo-50 dark:hover:bg-indigo-900 cursor-pointer">
                                            {{ area.nombre }}
                                        </option>
                                    </select>
                                    <div class="mt-2 text-xs text-gray-500 flex items-start gap-1">
                                        <span class="text-indigo-500 font-bold">Tip:</span>
                                        <span>Usa Ctrl (o Cmd) + Click para seleccionar múltiples áreas.</span>
                                    </div>
                                </div>
                                <p v-if="form.errors.area_ids" class="mt-1 text-sm text-red-600">{{ form.errors.area_ids }}</p>
                            </div>
                        </div>

                    </div>

                    <div class="px-8 py-5 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                        
                        <Link 
                            :href="expedienteRoutes.index() as unknown as string"
                            class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150"
                        >
                            <X class="w-4 h-4 mr-2" /> Cancelar
                        </Link>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 dark:shadow-none focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </AppLayout>
</template>
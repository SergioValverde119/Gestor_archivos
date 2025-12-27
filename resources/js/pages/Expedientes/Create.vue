<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Area } from '@/types';
import { Plus, X, FilePenLine, Building2, AlignLeft, FilePlus } from 'lucide-vue-next';

// --- IMPORTACIÓN DE WAYFINDER ---
import * as expedienteRoutes from '@/routes/expedientes';

// Props (Solo necesitamos las áreas para el select)
const props = defineProps<{
    areas: Area[];
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { 
        title: 'Expedientes', 
        href: expedienteRoutes.index() as unknown as string 
    },
    { title: 'Nuevo', href: '#' },
];

// Formulario (Limpio al inicio)
const form = useForm({
    numero_expediente: '',
    titulo: '',
    descripcion: '',
    area_ids: [] as number[], // Array vacío para selección múltiple
});

const submit = () => {
    // Usamos POST para crear
    form.post(expedienteRoutes.store() as unknown as string, {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Nuevo Expediente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-6">
            
            <form @submit.prevent="submit">
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                    
                    <div class="relative bg-gradient-to-r from-emerald-600 to-teal-500 px-8 py-6">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                                    <FilePlus class="w-6 h-6 text-emerald-100" />
                                    Nuevo Expediente
                                </h2>
                                <p class="mt-1 text-emerald-100 text-sm opacity-90">
                                    Apertura de una nueva carpeta digital.
                                </p>
                            </div>
                            <FilePlus class="w-24 h-24 text-white opacity-10 absolute -right-6 -bottom-10 rotate-12" />
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-12 gap-8">
                        
                        <div class="md:col-span-8 space-y-6">
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Título del Asunto <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        v-model="form.titulo"
                                        type="text"
                                        class="w-full pl-4 pr-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-lg font-medium transition-all"
                                        placeholder="Ej. Solicitud de Licencia de Construcción..."
                                        autofocus
                                    />
                                </div>
                                <p v-if="form.errors.titulo" class="mt-1 text-sm text-red-600 font-medium">{{ form.errors.titulo }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                        Folio / Número <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.numero_expediente"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-50 dark:bg-gray-800"
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
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 resize-none"
                                    placeholder="Detalles iniciales de apertura..."
                                ></textarea>
                                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-4 space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                                <label class="block text-sm font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    <Building2 class="w-4 h-4 text-emerald-600"/>
                                    Áreas Asignadas <span class="text-red-500">*</span>
                                </label>
                                
                                <div class="relative">
                                    <select 
                                        v-model="form.area_ids" 
                                        multiple
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 h-64 text-sm"
                                    >
                                        <option v-for="area in areas" :key="area.id" :value="area.id" class="p-2 border-b border-gray-100 dark:border-gray-600 last:border-0 hover:bg-emerald-50 dark:hover:bg-emerald-900 cursor-pointer">
                                            {{ area.nombre }}
                                        </option>
                                    </select>
                                    <div class="mt-2 text-xs text-gray-500 flex items-start gap-1">
                                        <span class="text-emerald-600 font-bold">Tip:</span>
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
                            class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-emerald-200 dark:shadow-none focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            <span v-if="form.processing">Creando...</span>
                            <span v-else>Crear Expediente</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </AppLayout>
</template>
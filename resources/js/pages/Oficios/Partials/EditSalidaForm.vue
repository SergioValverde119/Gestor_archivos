<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileOutput, Save, X, Building2, AlignLeft } from 'lucide-vue-next';
import * as oficioRoutes from '@/routes/oficios';

const props = defineProps<{
    oficio: any;
    areas: any[];
    users: any[];
    expedientes: any[];
    oficiosPosibles: any[];
}>();

const showSuccessMessage = ref(false);

const form = useForm({
    folio_salida: props.oficio.folio_salida,
    destinatario: props.oficio.destinatario,
    asunto: props.oficio.asunto,
    descripcion: props.oficio.descripcion,
    prioridad: props.oficio.prioridad || 'Ordinario',
    status: props.oficio.status || 'Enviado',
    oficio_respuesta_id: props.oficio.oficio_respuesta_id,
    expediente_id: props.oficio.expediente_id,
    area_ids: props.oficio.areas ? props.oficio.areas.map((a: any) => a.id) : [],
});

const submit = () => {
    const url = oficioRoutes.update({ oficio: props.oficio.id }) as unknown as string;

    form.put(url, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => showSuccessMessage.value = false, 3000);
        },
        onError: (e) => console.error(e)
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <div class="relative bg-gradient-to-r from-indigo-600 to-purple-500 px-8 py-6">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <FileOutput class="w-6 h-6 text-purple-100" />
                            Editar Salida
                        </h2>
                        <p class="mt-1 text-purple-100 text-sm opacity-90">
                            Folio Oficial: <span class="font-bold text-white">{{ oficio.folio_salida || 'Pendiente de Generar' }}</span>
                        </p>
                    </div>
                    <FileOutput class="w-24 h-24 text-white opacity-10 absolute -right-6 -bottom-10 rotate-12" />
                </div>
            </div>

            <div v-if="showSuccessMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-8 mt-6 rounded flex justify-between">
                <span>¡Oficio de salida actualizado correctamente!</span>
                <button @click="showSuccessMessage = false"><X class="w-4 h-4"/></button>
            </div>

            <div v-if="form.hasErrors" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-8 mt-6 rounded">
                <p class="font-bold">Error de Validación</p>
                <p>Revisa los campos del formulario.</p>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <div class="md:col-span-8 space-y-6">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Destinatario (¿A quién va?)</label>
                        <input v-model="form.destinatario" type="text" class="w-full pl-4 pr-4 py-3 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-medium" />
                        <p v-if="form.errors.destinatario" class="text-red-600 text-sm mt-1">{{ form.errors.destinatario }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Asunto</label>
                        <input v-model="form.asunto" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500" />
                        <p v-if="form.errors.asunto" class="text-red-600 text-sm mt-1">{{ form.errors.asunto }}</p>
                    </div>

                    <div class="bg-indigo-50 dark:bg-gray-700/30 p-4 rounded-lg border border-indigo-100 dark:border-gray-600">
                         <label class="block text-sm font-bold text-indigo-700 dark:text-indigo-300 mb-2">Respuesta a Oficio de Entrada</label>
                         <select v-model="form.oficio_respuesta_id" class="w-full rounded-md border-indigo-200 dark:bg-gray-700">
                             <option :value="null">-- No aplica (Oficio Inicial) --</option>
                             <option v-for="op in oficiosPosibles" :key="op.id" :value="op.id">
                                 [Entrada #{{ op.folio_interno }}] {{ op.asunto }}
                             </option>
                         </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                            <AlignLeft class="w-4 h-4 text-gray-400"/> Cuerpo del Oficio
                        </label>
                        <textarea v-model="form.descripcion" rows="6" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 resize-none focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="md:col-span-4 space-y-6">
                    
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prioridad</label>
                            <select v-model="form.prioridad" class="w-full rounded-md border-gray-300 dark:bg-gray-700">
                                <option>Ordinario</option>
                                <option>Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-100 mb-2">Archivar en Expediente</label>
                        <select v-model="form.expediente_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                            <option :value="null">-- Sin Expediente --</option>
                            <option v-for="exp in expedientes" :key="exp.id" :value="exp.id">{{ exp.titulo }}</option>
                        </select>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <Building2 class="w-4 h-4 text-indigo-500"/> Copia a Áreas
                        </label>
                        <select v-model="form.area_ids" multiple class="w-full rounded-lg border-gray-300 dark:bg-gray-700 h-40 text-sm">
                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-8 py-5 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                <Link :href="oficioRoutes.index() as unknown as string" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase hover:bg-gray-50 shadow-sm">
                    <X class="w-4 h-4 mr-2"/> Cancelar
                </Link>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase shadow-lg shadow-indigo-200 focus:ring-2 focus:ring-indigo-500 transition-all"
                    :class="{ 'opacity-75 cursor-wait': form.processing }"
                >
                    <Save class="w-4 h-4 mr-2" />
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Actualizar Salida</span>
                </button>
            </div>
        </div>
    </form>
</template>
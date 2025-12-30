<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Save, X, FileInput, Calendar, User, AlertTriangle, Building2, AlignLeft } from 'lucide-vue-next';
import * as oficioRoutes from '@/routes/oficios';

// Props con "any" para evitar conflictos de tipado profundo en el objeto Oficio
const props = defineProps<{
    oficio: any;
    areas: any[];
    users: any[];
    expedientes: any[];
}>();

// Estado para mensajes de feedback local
const showSuccessMessage = ref(false);

const form = useForm({
    folio_externo: props.oficio.folio_externo,
    remitente: props.oficio.remitente,
    asunto: props.oficio.asunto,
    descripcion: props.oficio.descripcion,
    // Fechas seguras
    fecha_recepcion: props.oficio.fecha_recepcion ? String(props.oficio.fecha_recepcion).split('T')[0] : '',
    fecha_limite: props.oficio.fecha_limite ? String(props.oficio.fecha_limite).split('T')[0] : '',
    prioridad: props.oficio.prioridad || 'Ordinario',
    status: props.oficio.status || 'Pendiente',
    // DGAF
    tiene_turno_dgaf: Boolean(props.oficio.tiene_turno_dgaf),
    folio_turno_dgaf: props.oficio.folio_turno_dgaf,
    fecha_turno_dgaf: props.oficio.fecha_turno_dgaf ? String(props.oficio.fecha_turno_dgaf).split('T')[0] : '',
    // Relaciones
    expediente_id: props.oficio.expediente_id,
    recibido_por_user_id: props.oficio.recibido_por_user_id,
    area_ids: props.oficio.areas ? props.oficio.areas.map((a: any) => a.id) : [],
});

const submit = () => {
    // CORRECCIÓN TS2589:
    // Calculamos la URL fuera y la casteamos a string para romper la inferencia profunda
    const url = oficioRoutes.update({ oficio: props.oficio.id }) as unknown as string;

    form.put(url, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => showSuccessMessage.value = false, 3000);
        },
        onError: (errors) => {
            console.error("Errores de validación:", errors);
        }
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <div class="relative bg-gradient-to-r from-blue-600 to-cyan-500 px-8 py-6">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <FileInput class="w-6 h-6 text-blue-100" />
                            Editar Entrada
                        </h2>
                        <p class="mt-1 text-blue-100 text-sm opacity-90">
                            Folio Interno: <span class="font-mono bg-white/20 px-2 py-0.5 rounded text-white font-bold">#{{ oficio.folio_interno }}</span>
                        </p>
                    </div>
                    <FileInput class="w-24 h-24 text-white opacity-10 absolute -right-6 -bottom-10 rotate-12" />
                </div>
            </div>

            <div v-if="showSuccessMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-8 mt-6 rounded shadow-sm flex items-center justify-between">
                <div>
                    <p class="font-bold">¡Guardado!</p>
                    <p class="text-sm">El oficio se actualizó correctamente.</p>
                </div>
                <button @click="showSuccessMessage = false" type="button" class="text-green-700 hover:text-green-900"><X class="w-5 h-5"/></button>
            </div>

            <div v-if="form.hasErrors" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-8 mt-6 rounded shadow-sm">
                <p class="font-bold">Error al guardar</p>
                <p class="text-sm">Por favor revisa los campos marcados en rojo.</p>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <div class="md:col-span-8 space-y-6">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Asunto del Trámite</label>
                        <input v-model="form.asunto" type="text" class="w-full pl-4 pr-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg font-medium" />
                        <p v-if="form.errors.asunto" class="text-red-600 text-sm mt-1">{{ form.errors.asunto }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Folio Externo (Papel)</label>
                            <input v-model="form.folio_externo" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.folio_externo" class="text-red-600 text-sm mt-1">{{ form.errors.folio_externo }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Remitente</label>
                            <input v-model="form.remitente" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.remitente" class="text-red-600 text-sm mt-1">{{ form.errors.remitente }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl border border-gray-100 dark:border-gray-600 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Recepción</label>
                            <input v-model="form.fecha_recepcion" type="date" class="w-full rounded-md border-gray-300 dark:bg-gray-700 text-sm" />
                            <p v-if="form.errors.fecha_recepcion" class="text-red-600 text-xs">{{ form.errors.fecha_recepcion }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Fecha Límite</label>
                            <input v-model="form.fecha_limite" type="date" class="w-full rounded-md border-gray-300 dark:bg-gray-700 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Prioridad</label>
                            <select v-model="form.prioridad" class="w-full rounded-md border-gray-300 dark:bg-gray-700 text-sm">
                                <option>Ordinario</option>
                                <option>Urgente</option>
                                <option>Extremadamente Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                            <AlignLeft class="w-4 h-4 text-gray-400"/> Descripción
                        </label>
                        <textarea v-model="form.descripcion" rows="4" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 resize-none focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <label class="flex items-center space-x-3 cursor-pointer mb-4">
                            <input v-model="form.tiene_turno_dgaf" type="checkbox" class="rounded text-purple-600 focus:ring-purple-500 w-5 h-5 border-gray-300" />
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">TIENE TURNO DGAF</span>
                        </label>

                        <div v-if="form.tiene_turno_dgaf" class="bg-purple-50 dark:bg-purple-900/10 p-4 rounded-lg border border-purple-100 dark:border-purple-800 grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in-down">
                            <div>
                                <label class="block text-xs font-bold text-purple-700 dark:text-purple-300 mb-1">Folio DGAF</label>
                                <input v-model="form.folio_turno_dgaf" type="text" class="w-full rounded-md border-purple-200 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-purple-700 dark:text-purple-300 mb-1">Fecha Turno</label>
                                <input v-model="form.fecha_turno_dgaf" type="date" class="w-full rounded-md border-purple-200 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4 space-y-6">
                    
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
                            <select v-model="form.status" class="w-full rounded-md border-gray-300 dark:bg-gray-700">
                                <option>Pendiente</option>
                                <option>En Proceso</option>
                                <option>Concluido</option>
                                <option>Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Registrado Por</label>
                            <select v-model="form.recibido_por_user_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700">
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                        <label class="block text-sm font-bold text-gray-800 dark:text-gray-100 mb-2">Vincular a Expediente</label>
                        <select v-model="form.expediente_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                            <option :value="null">-- Sin Expediente --</option>
                            <option v-for="exp in expedientes" :key="exp.id" :value="exp.id">{{ exp.titulo }}</option>
                        </select>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-xl border border-gray-100 dark:border-gray-600">
                        <label class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <Building2 class="w-4 h-4 text-blue-500"/> Áreas Asignadas
                        </label>
                        <select v-model="form.area_ids" multiple class="w-full rounded-lg border-gray-300 dark:bg-gray-700 h-40 text-sm">
                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-2">Usa Ctrl+Click para seleccionar varias.</p>
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
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase shadow-lg shadow-blue-200 focus:ring-2 focus:ring-blue-500 transition-all"
                    :class="{ 'opacity-75 cursor-wait': form.processing }"
                >
                    <Save class="w-4 h-4 mr-2" />
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar Cambios</span>
                </button>
            </div>

        </div>
    </form>
</template>
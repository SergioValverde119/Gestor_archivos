<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// --- Definiciones de Tipos ---
interface User { id: number; name: string; }

// Este componente recibe el objeto 'form' completo y la lista de todos los usuarios
const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    allUsers: User[];
}>();

// --- LÓGICA PARA LA FECHA LÍMITE OPCIONAL ---
const tieneFechaLimite = ref(!!props.form.fecha_limite);

// Observador para limpiar la fecha y ajustar la prioridad
watch(tieneFechaLimite, (newValue) => {
    if (!newValue) {
        props.form.fecha_limite = null;
        // Si se quita la fecha límite, regresa la prioridad a Ordinario
        if (props.form.prioridad === 'Urgente') {
            props.form.prioridad = 'Ordinario';
        }
    } else {
        // Si se añade una fecha límite, cambia la prioridad a Urgente
        props.form.prioridad = 'Urgente';
    }
});
// --- FIN DE LA LÓGICA ---

const authUser = computed(() => usePage().props.auth.user as User);

// Lógica para ordenar la lista de usuarios y poner al actual primero
const sortedAllUsers = computed(() => {
    if (!props.allUsers) return [];
    const currentUser = props.allUsers.find(u => u.id === authUser.value.id);
    const otherUsers = props.allUsers.filter(u => u.id !== authUser.value.id);
    return currentUser ? [currentUser, ...otherUsers] : props.allUsers;
});
</script>

<template>
    <div class="border-b dark:border-gray-700 pb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Datos del Oficio</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label for="folio_externo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Externo</label>
                <input type="text" id="folio_externo" v-model="form.folio_externo" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.folio_externo" class="text-red-500 text-sm mt-1">{{ form.errors.folio_externo }}</div>
            </div>

            <div>
                <label for="folio_interno" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio Interno (Generado)</label>
                <input type="text" id="folio_interno" v-model="form.folio_interno" disabled class="mt-1 block w-full rounded-md shadow-sm bg-gray-100 dark:bg-gray-700" />
            </div>




            <div>
                <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Recepción</label>
                <input type="date" id="fecha_recepcion" v-model="form.fecha_recepcion" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.fecha_recepcion" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_recepcion }}</div>
            </div>

            <div>
                <div class="flex items-center mb-1 h-6">
                     <input type="checkbox" id="tiene_fecha_limite" v-model="tieneFechaLimite" class="h-4 w-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                    <label for="tiene_fecha_limite" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">¿Tiene fecha límite?</label>
                </div>
                <input 
                    v-if="tieneFechaLimite"
                    type="date" 
                    id="fecha_limite" 
                    v-model="form.fecha_limite" 
                    class="block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"
                />
                 <div v-if="form.errors.fecha_limite" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_limite }}</div>
            </div>

            <div>
                <label for="prioridad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad</label>
                <select id="prioridad" v-model="form.prioridad" class="mt-1 block w-full rounded-md shadow-sm">
                    <option>Ordinario</option>
                    <option>Urgente</option>
                    <option>Extremadamente Urgente</option>
                </select>
            </div>

            <div>
                <label for="recibido_por_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recibido Por</label>
                <select id="recibido_por_user_id" v-model="form.recibido_por_user_id" required class="mt-1 block w-full rounded-md shadow-sm">
                    <option v-for="user in sortedAllUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
                <div v-if="form.errors.recibido_por_user_id" class="text-red-500 text-sm mt-1">{{ form.errors.recibido_por_user_id }}</div>
            </div>

                        <div class="md:col-span-2">
                <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</label>
                <input type="text" id="remitente" v-model="form.remitente" class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.remitente" class="text-red-500 text-sm mt-1">{{ form.errors.remitente }}</div>
            </div>

            
        </div>
    </div>
</template>
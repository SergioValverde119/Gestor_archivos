<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// --- Definiciones de Tipos ---
interface User { id: number; name: string; }

// Este componente recibe el objeto 'form' completo y la lista de todos los usuarios
const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    allUsers: User[];
}>();

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
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Paso 1: Datos del Oficio</h2>
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

            <div class="md:col-span-2">
                <label for="remitente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remitente</label>
                <input type="text" id="remitente" v-model="form.remitente" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.remitente" class="text-red-500 text-sm mt-1">{{ form.errors.remitente }}</div>
            </div>

            <div class="md:col-span-2">
                <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
                <input type="text" id="asunto" v-model="form.asunto" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
                <textarea id="descripcion" v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md shadow-sm"></textarea>
            </div>

            <div>
                <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Recepción</label>
                <input type="date" id="fecha_recepcion" v-model="form.fecha_recepcion" required class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.fecha_recepcion" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_recepcion }}</div>
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
        </div>
    </div>
</template>
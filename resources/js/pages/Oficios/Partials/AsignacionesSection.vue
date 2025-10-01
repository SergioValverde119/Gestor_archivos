<script setup lang="ts">
// --- Definiciones de Tipos ---
interface User { id: number; name: string; }

const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    users: User[]; // Lista de operativos
}>();

// Lógica para añadir y quitar asignaciones
const addAsignacion = () => {
    if (props.users.length > 0) {
        props.form.asignaciones.push({ user_id: null, permission: 'visualizador' });
    }
}
const removeAsignacion = (index: number) => {
    props.form.asignaciones.splice(index, 1);
}
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Asignar Permisos a Operativos</h2>
        <div v-for="(asignacion, index) in form.asignaciones" :key="index" class="flex items-center space-x-4 mb-2">
            <select v-model="asignacion.user_id" class="block w-1/2 rounded-md shadow-sm">
                <option :value="null" disabled>Seleccione un operativo</option>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
            <select v-model="asignacion.permission" class="block w-1/3 rounded-md shadow-sm">
                <option value="visualizador">Visualizador</option>
                <option value="editor">Editor</option>
            </select>
            <button type="button" @click="removeAsignacion(index)" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
        <button type="button" @click="addAsignacion" class="mt-2 text-sm text-blue-600 hover:text-blue-800" v-if="users.length > 0">+ Añadir Asignación</button>
        <div v-if="form.errors.asignaciones" class="text-red-500 text-sm mt-1">{{ form.errors.asignaciones }}</div>
    </div>
</template>
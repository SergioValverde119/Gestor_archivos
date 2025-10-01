<script setup lang="ts">
// --- Definiciones de Tipos ---
interface Area { id: number; nombre: string; }

const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    areas: Area[];
}>();

// La lógica para añadir y quitar se queda aquí para manejar el array local
const addArea = () => {
    props.form.area_ids.push(null);
}
const removeArea = (index: number) => {
    props.form.area_ids.splice(index, 1);
}
</script>

<template>
    <div class="border-b dark:border-gray-700 pb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4"> Áreas Involucradas</h2>
        <div v-for="(areaId, index) in form.area_ids" :key="index" class="flex items-center space-x-4 mb-2">
            <select v-model="form.area_ids[index]" class="block w-full rounded-md shadow-sm">
                <option :value="null" disabled>Seleccione un área</option>
                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
            </select>
            <button type="button" @click="removeArea(index)" class="text-red-500 hover:text-red-700" v-if="form.area_ids.length > 1">&times;</button>
        </div>
        <button type="button" @click="addArea" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
            + Añadir Área
        </button>

        <div v-if="form.errors.area_ids" class="text-red-500 text-sm mt-1">
            {{ form.errors.area_ids }}
        </div>
    </div>
</template>
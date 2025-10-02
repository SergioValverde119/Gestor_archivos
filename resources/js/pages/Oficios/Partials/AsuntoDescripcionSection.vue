<script setup lang="ts">
// Este componente recibe el objeto 'form' completo desde el padre.
const props = defineProps<{
    form: any; // El objeto useForm de Inertia
}>();
</script>

<template>
    <div class="md:col-span-2">
        <label for="asunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asunto</label>
        <input type="text" id="asunto" v-model="form.asunto"  class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500" />
        <div v-if="form.errors.asunto" class="text-red-500 text-sm mt-1">{{ form.errors.asunto }}</div>
    </div>

    <div class="md:col-span-2">
        <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción (Opcional)</label>
        <textarea id="descripcion" v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
</template>

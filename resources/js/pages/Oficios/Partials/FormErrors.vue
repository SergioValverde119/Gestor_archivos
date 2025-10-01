<script setup lang="ts">
// Este componente recibe el objeto 'form' de Inertia como una propiedad.
const props = defineProps<{
  form: any;
}>();
</script>

<template>
    <!-- Muestra el panel solo si el formulario tiene errores. -->
    <div v-if="form.hasErrors" class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-md">
        <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Hubo un problema con tu envío</h3>
        <ul class="list-disc list-inside mt-2 text-sm text-red-700 dark:text-red-400">
            <!-- Itera sobre todos los errores del formulario y los muestra en una lista. -->
            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
        </ul>
    </div>
</template>

<script setup lang="ts">
// Este componente recibe el objeto 'form' completo desde el padre.
const props = defineProps<{
    form: any; // El objeto useForm de Inertia
}>();
</script>

<template>
    <div class="border-b dark:border-gray-700 pb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Turno D.G.A.F. (Opcional)</h2>
        <div class="space-y-4">
            <!-- Checkbox para activar la sección -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="tiene_turno_dgaf" 
                    v-model="form.tiene_turno_dgaf" 
                    class="h-4 w-4 text-blue-600 rounded"
                />
                <label for="tiene_turno_dgaf" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    El oficio tiene un turno de la D.G.A.F.
                </label>
            </div>

            <!-- Campos condicionales que aparecen si el checkbox está marcado -->
            <div v-if="form.tiene_turno_dgaf" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 p-4 border dark:border-gray-700 rounded-md">
                <div>
                    <label for="folio_turno_dgaf" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Folio del Turno D.G.A.F.</label>
                    <input type="text" id="folio_turno_dgaf" v-model="form.folio_turno_dgaf" required class="mt-1 block w-full rounded-md shadow-sm" />
                    <div v-if="form.errors.folio_turno_dgaf" class="text-red-500 text-sm mt-1">{{ form.errors.folio_turno_dgaf }}</div>
                </div>
                <div>
                    <label for="fecha_turno_dgaf" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha del Turno D.G.A.F.</label>
                    <input type="date" id="fecha_turno_dgaf" v-model="form.fecha_turno_dgaf" required class="mt-1 block w-full rounded-md shadow-sm" />
                    <div v-if="form.errors.fecha_turno_dgaf" class="text-red-500 text-sm mt-1">{{ form.errors.fecha_turno_dgaf }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
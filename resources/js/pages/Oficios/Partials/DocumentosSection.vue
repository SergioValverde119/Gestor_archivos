<script setup lang="ts">
const props = defineProps<{
    form: any; // El objeto useForm de Inertia
}>();

const handleAnexosChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        props.form.anexos = Array.from(target.files);
    }
}
</script>

<template>
    <div class="border-b dark:border-gray-700 pb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Adjuntar Documentos</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="documento_principal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Documento Principal</label>
                <input type="file" id="documento_principal" @input="form.documento_principal = ($event.target as HTMLInputElement).files?.[0] ?? null" class="mt-1 block w-full text-sm" />
                <div v-if="form.errors.documento_principal" class="text-red-500 text-sm mt-1">{{ form.errors.documento_principal }}</div>
            </div>
            <div>
                <label for="anexos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anexos (Opcional, puede seleccionar varios)</label>
                <input type="file" id="anexos" @change="handleAnexosChange" multiple class="mt-1 block w-full text-sm" />
                <div v-if="form.errors.anexos" class="text-red-500 text-sm mt-1">{{ form.errors.anexos }}</div>
            </div>
        </div>
    </div>
</template>
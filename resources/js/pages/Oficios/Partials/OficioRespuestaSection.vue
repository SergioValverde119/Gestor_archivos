<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { type SearchableOficio } from '@/types';


const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    searchableOficios: SearchableOficio[];
}>();

// --- Lógica para la Búsqueda ---
const searchTerm = ref('');
const isListVisible = ref(false);
const rootEl = ref<HTMLElement | null>(null); // Referencia al div principal

// Observador para invalidar la selección si el usuario edita el texto manualmente
watch(searchTerm, (newValue) => {
    if (props.form.oficio_respuesta_id) {
        const selectedOficio = props.searchableOficios.find(o => o.id === props.form.oficio_respuesta_id);
        const folioOficio = selectedOficio?.folio_externo || selectedOficio?.folio_salida;
        let expectedText = `Oficio: ${folioOficio}`;
        if (selectedOficio?.folio_interno) {
            expectedText += ` (Interno: ${selectedOficio.folio_interno})`;
        }
        
        if (newValue !== expectedText) {
            props.form.oficio_respuesta_id = null; // Invalida la selección
        }
    }
});

const filteredOficios = computed(() => {
    const search = searchTerm.value.toLowerCase();
    // Si no hay búsqueda, muestra los 5 más recientes. Si hay búsqueda, filtra.
    const source = searchTerm.value ? props.searchableOficios : [...props.searchableOficios].reverse();
    
    return source.filter(oficio => 
        (oficio.folio_interno && oficio.folio_interno.toLowerCase().includes(search)) ||
        (oficio.folio_externo && oficio.folio_externo.toLowerCase().includes(search)) ||
        (oficio.folio_salida && oficio.folio_salida.toLowerCase().includes(search)) ||
        (oficio.asunto && oficio.asunto.toLowerCase().includes(search))
    ).slice(0, 5);
});

const selectOficio = (oficio: SearchableOficio) => {
    props.form.oficio_respuesta_id = oficio.id;
    const folioOficio = oficio.folio_externo || oficio.folio_salida;
    let displayText = `Oficio: ${folioOficio}`;
    if (oficio.folio_interno) {
        displayText += ` (Interno: ${oficio.folio_interno})`;
    }
    searchTerm.value = displayText;
    isListVisible.value = false;
};

const clearSelection = () => {
    props.form.oficio_respuesta_id = null;
    searchTerm.value = '';
};

// --- Lógica para cerrar la lista al hacer clic afuera ---
const handleFocusOut = (event: FocusEvent) => {
    // Si el nuevo elemento enfocado NO está dentro de este componente, se cierra la lista.
    if (rootEl.value && !rootEl.value.contains(event.relatedTarget as Node)) {
        isListVisible.value = false;
    }
}
</script>

<template>
    <!-- Se envuelve todo en un div con @focusout para manejar el cierre de la lista -->
    <div class="md:col-span-2 relative" ref="rootEl" @focusout="handleFocusOut">
        <label for="oficio_respuesta_search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Responder a Oficio (Opcional)</label>
        <div class="relative">
            <input 
                type="text"
                id="oficio_respuesta_search"
                v-model="searchTerm"
                placeholder="Buscar por cualquier folio o asunto..."
                @focus="isListVisible = true"
                :disabled="!!form.oficio_respuesta_id"
                class="mt-1 block w-full rounded-md shadow-sm"
                autocomplete="off"
            />
            <button 
                v-if="form.oficio_respuesta_id" 
                @click="clearSelection"
                type="button" 
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-red-500"
            >
                &times;
            </button>
        </div>
        
        <div v-if="isListVisible && !form.oficio_respuesta_id" class="absolute z-10 w-full bg-white dark:bg-gray-900 border dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <ul>
                <li 
                    v-for="oficio in filteredOficios" 
                    :key="oficio.id" 
                    @mousedown.prevent="selectOficio(oficio)"
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <div class="flex flex-col">
                        <span class="font-semibold">Oficio: {{ oficio.folio_externo || oficio.folio_salida }}</span>
                        <span v-if="oficio.folio_interno" class="text-xs text-gray-500">Interno: {{ oficio.folio_interno }} | Asunto: {{ oficio.asunto }}</span>
                        <span v-else class="text-xs text-gray-500">Asunto: {{ oficio.asunto }}</span>
                    </div>
                </li>
                <li v-if="filteredOficios.length === 0 && searchTerm" class="px-4 py-2 text-sm text-gray-500">
                    No se encontraron coincidencias.
                </li>
            </ul>
        </div>
        <div v-if="form.errors.oficio_respuesta_id" class="text-red-500 text-sm mt-1">{{ form.errors.oficio_respuesta_id }}</div>
    </div>
</template>

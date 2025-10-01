<script setup lang="ts">
import { ref, computed, watch } from 'vue';

// --- Definiciones de Tipos ---
interface SearchableOficio { 
    id: number; 
    folio_interno: string | null;
    folio_externo: string | null;
    folio_salida: string | null;
    asunto: string; 
}

const props = defineProps<{
    form: any; // El objeto useForm de Inertia
    searchableOficios: SearchableOficio[];
}>();

// --- Lógica para la Búsqueda ---
const searchTerm = ref('');
const isListVisible = ref(false);

// Observador para invalidar la selección si el usuario edita el texto
watch(searchTerm, (newValue) => {
    // Si hay un ID seleccionado pero el texto ya no coincide, se resetea la selección.
    // Esto fuerza al usuario a elegir siempre de la lista.
    if (props.form.oficio_respuesta_id) {
        const selectedOficio = props.searchableOficios.find(o => o.id === props.form.oficio_respuesta_id);
        const expectedText = `Folio Interno: ${selectedOficio?.folio_interno} - ${selectedOficio?.asunto}`;
        if (newValue !== expectedText) {
            props.form.oficio_respuesta_id = null;
        }
    }
});

const filteredOficios = computed(() => {
    const search = searchTerm.value.toLowerCase();
    // Si no hay búsqueda, muestra los más recientes. Si hay búsqueda, filtra.
    const source = searchTerm.value ? props.searchableOficios : [...props.searchableOficios].reverse();
    
    return source.filter(oficio => 
        (oficio.folio_interno && oficio.folio_interno.toLowerCase().includes(search)) ||
        (oficio.folio_externo && oficio.folio_externo.toLowerCase().includes(search)) ||
        (oficio.folio_salida && oficio.folio_salida.toLowerCase().includes(search)) ||
        (oficio.asunto && oficio.asunto.toLowerCase().includes(search))
    ).slice(0, 5); // Limitar a 10 resultados
});

const selectOficio = (oficio: SearchableOficio) => {
    props.form.oficio_respuesta_id = oficio.id;
    searchTerm.value = `Folio Interno: ${oficio.folio_interno} - ${oficio.asunto}`;
    isListVisible.value = false;
};

const clearSelection = () => {
    props.form.oficio_respuesta_id = null;
    searchTerm.value = '';
};
</script>

<template>
    <!-- Se envuelve todo en un div con @focusout para manejar el cierre de la lista -->
    <div class="md:col-span-2 relative" @focusout="isListVisible = false">
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
                    <span class="font-semibold">Interno: {{ oficio.folio_interno }}</span> 
                    <span class="text-sm text-gray-600 dark:text-gray-400"> - {{ oficio.asunto }}</span>
                </li>
                <li v-if="filteredOficios.length === 0 && searchTerm" class="px-4 py-2 text-sm text-gray-500">
                    No se encontraron coincidencias.
                </li>
                 <li v-if="!searchTerm" class="px-4 py-2 text-sm text-gray-500">
                    Escribe para empezar a buscar...
                </li>
            </ul>
        </div>
        <div v-if="form.errors.oficio_respuesta_id" class="text-red-500 text-sm mt-1">{{ form.errors.oficio_respuesta_id }}</div>
    </div>
</template>
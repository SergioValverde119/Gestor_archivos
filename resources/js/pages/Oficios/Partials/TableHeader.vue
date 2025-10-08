<script setup lang="ts">
import { ArrowUp, ArrowDown, ChevronDown, Filter } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

// --- Se define la "forma" que tendrá el objeto de filtros ---
interface Area { id: number; nombre: string; }
interface Filters {
  search: string;
  sort?: string;
  direction?: 'asc' | 'desc';
  tiene_turno_dgaf: 'true' | 'false' | null;
  tipo?: 'entrada' | 'salida' | null;
  date_from?: string | null;
  date_to?: string | null;
  recepcion_from?: string | null;
  recepcion_to?: string | null;
  limite_from?: string | null;
  limite_to?: string | null;
  area_ids?: number[];
}

const props = defineProps<{
  visibleHeaders: { key: string; label: string; }[];
  filters: Filters;
  areas: Area[];
}>();

const emit = defineEmits(['sort', 'update:filters']);

const setSort = (column: string, direction: 'asc' | 'desc') => {
    emit('sort', { column, direction });
};

const updateFilter = (key: keyof Filters, value: any) => {
    const newFilters = { ...props.filters, [key]: value };
    emit('update:filters', newFilters);
}

// --- Lógica para el manejo de menús ---
const headerRef = ref<HTMLElement | null>(null);

// Cierra otros menús cuando se abre uno nuevo
const handleHeaderClick = (event: MouseEvent) => {
    if (!(event.target instanceof HTMLElement) || event.target.tagName !== 'SUMMARY') {
        return;
    }
    const currentDetails = event.target.closest('details');
    if (headerRef.value) {
        headerRef.value.querySelectorAll('details').forEach(detail => {
            if (detail !== currentDetails) {
                detail.removeAttribute('open');
            }
        });
    }
};

// Cierra los menús si se hace clic fuera de la cabecera
onMounted(() => {
    document.addEventListener('click', (event: MouseEvent) => {
        if (headerRef.value && !headerRef.value.contains(event.target as Node)) {
            headerRef.value.querySelectorAll('details[open]').forEach(detail => {
                detail.removeAttribute('open');
            });
        }
    }, true);
});

</script>

<template>
    <thead class="bg-gray-50 dark:bg-gray-700" ref="headerRef" @click="handleHeaderClick">
        <tr>
            <th 
                v-for="header in visibleHeaders" 
                :key="header.key" 
                class="px-6 py-3 text-left text-xs font-medium uppercase"
            >
                <!-- Menú para columnas ordenables (sin filtro de fecha) -->
                <details v-if="['folio', 'asunto', 'status', 'prioridad'].includes(header.key)" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <span v-if="filters.sort === header.key">
                            <ArrowUp v-if="filters.direction === 'asc'" class="w-4 h-4 ml-1" />
                            <ArrowDown v-else class="w-4 h-4 ml-1" />
                        </span>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block">
                        <a @click.prevent="setSort(header.key, 'asc')" class="block px-4 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">Ordenar A-Z / Asc</a>
                        <a @click.prevent="setSort(header.key, 'desc')" class="block px-4 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">Ordenar Z-A / Desc</a>
                    </div>
                </details>

                                <!-- NUEVO: Menú para el filtro de Áreas -->
                <details v-else-if="header.key === 'areas'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.area_ids && filters.area_ids.length > 0" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-3 space-y-2 max-h-60 overflow-y-auto">
                        <label v-for="area in areas" :key="area.id" class="flex items-center text-sm cursor-pointer">
                            <input 
                                type="checkbox"
                                :value="area.id"
                                v-model="filters.area_ids"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2">{{ area.nombre }}</span>
                        </label>
                    </div>
                </details>
                
                <!-- Menú para el filtro de Fecha de Registro -->
                <details v-else-if="header.key === 'fechaRegistro'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.date_from || filters.date_to" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-3 space-y-3">
                        <div>
                            <label for="date_from" class="block text-xs font-medium mb-1">Desde:</label>
                            <input type="date" id="date_from" :value="filters.date_from" @input="updateFilter('date_from', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium mb-1">Hasta:</label>
                            <input type="date" id="date_to" :value="filters.date_to" @input="updateFilter('date_to', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <a @click.prevent="setSort('fechaRegistro', 'asc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Ascendente</a>
                            <a @click.prevent="setSort('fechaRegistro', 'desc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Descendente</a>
                        </div>
                    </div>
                </details>

                <!-- Menú para el filtro de Fecha de Recepción -->
                <details v-else-if="header.key === 'fechaRecepcion'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.recepcion_from || filters.recepcion_to" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-3 space-y-3">
                        <div>
                            <label for="recepcion_from" class="block text-xs font-medium mb-1">Desde:</label>
                            <input type="date" id="recepcion_from" :value="filters.recepcion_from" @input="updateFilter('recepcion_from', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                        <div>
                            <label for="recepcion_to" class="block text-xs font-medium mb-1">Hasta:</label>
                            <input type="date" id="recepcion_to" :value="filters.recepcion_to" @input="updateFilter('recepcion_to', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                         <div class="border-t pt-2 mt-2">
                            <a @click.prevent="setSort('fechaRecepcion', 'asc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Ascendente</a>
                            <a @click.prevent="setSort('fechaRecepcion', 'desc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Descendente</a>
                        </div>
                    </div>
                </details>

                <!-- Menú para el filtro de Fecha Límite -->
                <details v-else-if="header.key === 'fechaLimite'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.limite_from || filters.limite_to" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-3 space-y-3">
                        <div>
                            <label for="limite_from" class="block text-xs font-medium mb-1">Desde:</label>
                            <input type="date" id="limite_from" :value="filters.limite_from" @input="updateFilter('limite_from', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                        <div>
                            <label for="limite_to" class="block text-xs font-medium mb-1">Hasta:</label>
                            <input type="date" id="limite_to" :value="filters.limite_to" @input="updateFilter('limite_to', ($event.target as HTMLInputElement).value)" class="w-full text-sm rounded-md"/>
                        </div>
                         <div class="border-t pt-2 mt-2">
                            <a @click.prevent="setSort('fechaLimite', 'asc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Ascendente</a>
                            <a @click.prevent="setSort('fechaLimite', 'desc')" class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md">Ordenar Descendente</a>
                        </div>
                    </div>
                </details>

                <!-- Menú para el filtro de Turno DGAF -->
                <details v-else-if="header.key === 'turnoDGAF'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.tiene_turno_dgaf !== null && filters.tiene_turno_dgaf !== undefined" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-2">
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="filters.tiene_turno_dgaf === null" @change="updateFilter('tiene_turno_dgaf', null)" class="mr-2"/> Todos</label>
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="filters.tiene_turno_dgaf === 'true'" @change="updateFilter('tiene_turno_dgaf', 'true')" class="mr-2"/> Solo con turno</label>
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="filters.tiene_turno_dgaf === 'false'" @change="updateFilter('tiene_turno_dgaf', 'false')" class="mr-2"/> Solo sin turno</label>
                    </div>
                </details>
                
                <!-- Menú para el filtro de Tipo -->
                <details v-else-if="header.key === 'tipo'" class="relative group">
                    <summary class="flex items-center cursor-pointer list-none -ml-2 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ header.label }}
                        <Filter v-if="filters.tipo" class="w-3 h-3 ml-1 text-blue-500"/>
                        <ChevronDown class="w-4 h-4 ml-auto opacity-60 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute z-10 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg hidden group-open:block p-2">
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="!filters.tipo" @change="updateFilter('tipo', null)" class="mr-2"/> Todos</label>
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="filters.tipo === 'entrada'" @change="updateFilter('tipo', 'entrada')" class="mr-2"/> Solo Entrada</label>
                        <label class="block px-2 py-1 text-sm cursor-pointer hover:bg-gray-100 rounded-md"><input type="radio" :checked="filters.tipo === 'salida'" @change="updateFilter('tipo', 'salida')" class="mr-2"/> Solo Salida</label>
                    </div>
                </details>
                
                <!-- Cabeceras normales (no interactivas) -->
                <span v-else>{{ header.label }}</span>
            </th>
        </tr>
    </thead>
</template>

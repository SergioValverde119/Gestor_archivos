<script setup lang="ts">
import { ref } from 'vue';
import { Search, ChevronDown } from 'lucide-vue-next';

// --- NUEVO: Se define la "forma" que tendrá el objeto de filtros ---
interface Filters {
  search: string;
  tiene_turno_dgaf: 'true' | 'false' | null;
}

// Este componente usa v-model para una comunicación bidireccional con el padre.
// Ahora se le indica a defineModel qué tipo de datos va a manejar.
const filters = defineModel<Filters>('filters', { required: true, default: () => ({ search: '', tiene_turno_dgaf: null }) });
const visibleColumns = defineModel<string[]>('visibleColumns');

// Definición de las columnas que se pueden mostrar
const allColumns = ref([
    { key: 'acciones', label: 'Acciones' },
    { key: 'tipo', label: 'Tipo' },
    { key: 'folio', label: 'Folio' },
    { key: 'asunto', label: 'Asunto' },
    { key: 'status', label: 'Estado' },
    { key: 'prioridad', label: 'Prioridad' },
    { key: 'fechaRegistro', label: 'Fecha Reg.' },
    { key: 'folioInterno', label: 'Folio Interno' },
    { key: 'expediente', label: 'Expediente' },
    { key: 'remitente_destinatario', label: 'Remitente / Dest.' },
    { key: 'descripcion', label: 'Descripción' },
    { key: 'fechaRecepcion', label: 'Fecha Recep.' },
    { key: 'fechaLimite', label: 'Fecha Límite' },
    { key: 'registradoPor', label: 'Registrado Por' },
    { key: 'areas', label: 'Áreas' },
    { key: 'asignadoA', label: 'Asignado A' },
    { key: 'respondeA', label: 'Responde A' },
    { key: 'turnoDGAF', label: 'Turno DGAF' },
    { key: 'anexos', label: 'Anexos' },
]);
</script>

<template>
    <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <!-- Barra de Búsqueda -->
        <div class="relative w-full md:w-1/2">
          <input
            type="text"
            v-model="filters.search"
            placeholder="Buscar por folio, asunto, remitente o destinatario..."
            class="w-full rounded-md shadow-sm pl-10"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="h-5 w-5 text-gray-400" />
          </div>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Filtro de Turno DGAF -->
            <div>
                <label for="turno_filter" class="sr-only">Filtrar por Turno DGAF</label>
                <select id="turno_filter" v-model="filters.tiene_turno_dgaf" class="w-full rounded-md shadow-sm">
                    <option :value="null">Todos (Turno DGAF)</option>
                    <option value="true">Solo con turno</option>
                    <option value="false">Solo sin turno</option>
                </select>
            </div>

            <!-- Selector de Columnas -->
            <div class="relative">
                <details class="group">
                    <summary class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-md cursor-pointer">
                        Columnas <ChevronDown class="w-4 h-4 group-open:rotate-180 transition-transform"/>
                    </summary>
                    <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md shadow-lg z-10">
                        <div class="p-2 grid grid-cols-2 gap-2">
                            <label v-for="col in allColumns" :key="col.key" class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" :value="col.key" v-model="visibleColumns" class="rounded"/>
                                <span>{{ col.label }}</span>
                            </label>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</template>

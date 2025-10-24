<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';

// --- Se importan los tipos desde el archivo central ---
import { type Area, type Oficio, type PaginatedOficios, type OficioFilters } from '@/types';

// --- CORRECCIÓN: Se marca el modelo de filtros como obligatorio ---
const filters = defineModel<OficioFilters>('filters', { required: true });

const props = defineProps<{
  oficios: PaginatedOficios;
  visibleHeaders: { key: string; label: string; }[];
  areas: Area[];
}>();

const emit = defineEmits(['sort']);

// La función de ordenamiento ahora solo retransmite el evento
const handleSort = (payload: { column: string, direction: 'asc' | 'desc' }) => {
    emit('sort', payload);
};

// Lógica para las filas de relleno (para que la tabla no se encoja)
const placeholderRowCount = computed(() => {
  const minRows = 8;
  const dataLength = props.oficios.data.length;
  // Solo añade placeholders si hay al menos una fila, pero menos que el mínimo
  if (dataLength > 0 && dataLength < minRows) {
    return minRows - dataLength;
  }
  return 0;
});

</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            
            <TableHeader 
                :visible-headers="visibleHeaders" 
                v-model:filters="filters" 
                :areas="props.areas"
                @sort="handleSort"
            />

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="oficios.data.length === 0">
                <td :colspan="visibleHeaders.length" class="px-6 py-20 text-center text-sm text-gray-500">No se encontraron oficios.</td>
              </tr>
              <TableRow 
                v-for="oficio in oficios.data" 
                :key="oficio.id" 
                :oficio="oficio"
                :visible-headers="visibleHeaders"
              />
               <!-- Se renderizan las filas de relleno para mantener la altura -->
               <tr v-for="n in placeholderRowCount" :key="`placeholder-${n}`">
                  <td :colspan="visibleHeaders.length" class="px-6 py-4 h-[65px]">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación y Selector de "por página" -->
        <div v-if="oficios.links.length > 3" class="flex items-center justify-between mt-8">
            <div class="flex items-center space-x-2 text-sm">
                <label for="per_page">Mostrar:</label>
                <!-- CORRECCIÓN: Ahora 'filters' nunca será undefined -->
                <select id="per_page" v-model="filters.per_page" class="rounded-md shadow-sm border-gray-300">
                    <option value="8">8</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-gray-600">registros</span>
            </div>

            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <Link
                v-for="(link, key) in oficios.links"
                :key="key"
                :href="link.url ?? '#'"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                :class="{'bg-blue-600 text-white': link.active, 'hover:bg-gray-50': !link.active, 'opacity-50': !link.url}"
                v-html="link.label"
              />
            </nav>
        </div>
      </div>
</template>

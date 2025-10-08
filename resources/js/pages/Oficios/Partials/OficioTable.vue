<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowUp, ArrowDown, ArrowRight, ArrowLeft } from 'lucide-vue-next';
import OficioActionButtons from './OficioActionButtons.vue';
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';

// --- Definiciones de Tipos ---
interface Documento { id: number; nombre_documento: string; ruta_almacenamiento: string; rol_documento: 'principal' | 'anexo'; }
interface Area { id: number; nombre: string; }
interface Expediente { id: number; numero_expediente: string; areas: Area[]; }
interface User { id: number; name: string; }
interface Permission { user: User; }
interface Oficio {
  id: number;
  tipo: 'entrada' | 'salida';
  folio_externo: string | null;
  folio_salida: string | null;
  folio_interno: string | null;
  remitente: string | null;
  destinatario: string | null;
  asunto: string;
  descripcion: string | null;
  status: string;
  prioridad: string;
  fecha_recepcion: string | null;
  fecha_limite: string | null;
  tiene_turno_dgaf: boolean;
  folio_turno_dgaf: string | null;
  fecha_turno_dgaf: string | null;
  expediente: Expediente | null;
  recibidoPor: User | null;
  created_at: string;
  documentos: Documento[];
  permissions: Permission[];
  respuestaA: { id: number; folio_interno: string; } | null;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedOficios { data: Oficio[]; links: PaginationLink[]; }


const props = defineProps<{
  oficios: PaginatedOficios;
  visibleHeaders: { key: string; label: string; }[];
  filters: any;
  areas: Area[]; // <-- Se asegura de que reciba la lista de áreas
}>();

const emit = defineEmits(['sort', 'update:filters']);

const handleSort = (payload: { column: string, direction: 'asc' | 'desc' }) => {
    emit('sort', payload);
};

const handleFilterUpdate = (newFilters: any) => {
    emit('update:filters', newFilters);
}

const placeholderRowCount = computed(() => {
  const minRows = 8;
  const dataLength = props.oficios.data.length;
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
                :filters="filters" 
                :areas="props.areas"
                @sort="handleSort"
                @update:filters="handleFilterUpdate"
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
               <tr v-for="n in placeholderRowCount" :key="`placeholder-${n}`">
                  <td :colspan="visibleHeaders.length" class="px-6 py-4 h-[65px]">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-if="oficios.links.length > 3" class="flex items-center justify-between mt-8">
            <div class="flex items-center space-x-2 text-sm">
                <label for="per_page">Mostrar:</label>
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


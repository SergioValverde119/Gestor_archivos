<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Eye, Edit, Download, FileText } from 'lucide-vue-next';
import { computed } from 'vue';

// --- Definición de Tipos ---
interface Documento { 
    id: number; 
    nombre_documento: string;
    ruta_almacenamiento: string; 
    rol_documento: 'principal' | 'anexo';
}

interface Oficio {
  id: number;
  documentos: Documento[]; // El componente recibe la lista completa de documentos
}

const props = defineProps<{
  oficio: Oficio;
}>();

// --- Lógica para encontrar el documento principal ---
const documentoPrincipal = computed(() => {
    if (!props.oficio.documentos || props.oficio.documentos.length === 0) {
        return null;
    }
    return props.oficio.documentos.find(doc => doc.rol_documento === 'principal');
});

</script>

<template>
    <div class="flex justify-start space-x-4 items-center">
        <!-- Botón de Descarga -->
        <a 
            v-if="documentoPrincipal" 
            :href="`/documentos/${documentoPrincipal.id}/download`" 
            class="text-blue-500 hover:text-blue-700" 
            title="Descargar Documento Principal"
        >
            <FileText class="w-5 h-5" />
        </a>
        <span v-else class="text-gray-400 w-5 h-5 flex items-center justify-center" title="Sin documento principal adjunto">-</span>
        
        <!-- Botón de Visualizar -->
        <Link 
            :href="`/oficios/${oficio.id}`" 
            class="text-green-600 hover:text-green-800" 
            title="Ver Detalles del Oficio"
        >
            <Eye class="w-5 h-5" />
        </Link>

        <!-- Botón de Editar -->
        <Link 
            :href="`/oficios/${oficio.id}/edit`" 
            class="text-indigo-600 hover:text-indigo-800" 
            title="Editar Oficio"
        >
          <Edit class="w-5 h-5" />
        </Link>
    </div>
</template>
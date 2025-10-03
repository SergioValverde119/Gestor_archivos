<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Eye, Edit, Download } from 'lucide-vue-next';

// --- Definición de Tipos ---
interface Documento { 
    id: number; 
    ruta_almacenamiento: string; 
}

interface Oficio {
  id: number;
  documentoPrincipal?: Documento | null;
}

const props = defineProps<{
  oficio: Oficio;
}>();
</script>

<template>
    <div class="flex justify-start space-x-4 items-center">
        <!-- Botón de Descarga (Ahora siempre visible) -->
        <a 
            :href="oficio.documentoPrincipal ? `/documentos/${oficio.documentoPrincipal.id}/download` : '#'" 
            :class="{
                'text-blue-500 hover:text-blue-700': oficio.documentoPrincipal,
                'text-gray-400 pointer-events-none': !oficio.documentoPrincipal
            }"
            title="Descargar Documento Principal"
            @click.prevent="!oficio.documentoPrincipal && $event.preventDefault()"
        >
            <Download class="w-5 h-5" />
        </a>
        
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

<script setup lang="ts">
import { computed } from 'vue';
import { Download, File } from 'lucide-vue-next';
import { type Oficio } from '@/types';
const props = defineProps<{
  oficio: Oficio;
}>();

// Propiedades computadas para separar el documento principal de los anexos
const documentoPrincipal = computed(() => 
    props.oficio.documentos.find(doc => doc.rol_documento === 'principal')
);

const anexos = computed(() => 
    props.oficio.documentos.filter(doc => doc.rol_documento === 'anexo')
);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-4">Documentos Adjuntos</h3>
        <div v-if="oficio.documentos.length > 0" class="space-y-3">
            <!-- Mostrar el Documento Principal -->
            <div v-if="documentoPrincipal">
                <a :href="`/documentos/${documentoPrincipal.id}/download`" class="flex items-center p-2 rounded-md transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                    <Download class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" />
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ documentoPrincipal.nombre_documento }}</span>
                    <span class="ml-auto text-xs font-semibold text-green-600 dark:text-green-400 px-2 py-1 bg-green-100 dark:bg-green-900 rounded-full">Principal</span>
                </a>
            </div>

            <!-- Lista de Anexos -->
            <div v-if="anexos.length > 0" class="pt-3 border-t dark:border-gray-700">
                 <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Anexos:</p>
                 <ul class="space-y-2">
                    <li v-for="anexo in anexos" :key="anexo.id">
                        <a :href="`/documentos/${anexo.id}/download`" class="flex items-center p-2 rounded-md transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                            <File class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ anexo.nombre_documento }}</span>
                        </a>
                    </li>
                 </ul>
            </div>
        </div>
        <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay documentos adjuntos para este oficio.</p>
    </div>
</template>
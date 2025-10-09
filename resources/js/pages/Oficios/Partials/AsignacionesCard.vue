<script setup lang="ts">
import { Users, Building } from 'lucide-vue-next';

// --- Definición de Tipos ---
interface Area {
    id: number;
    nombre: string;
}
interface User {
    id: number;
    name: string;
}
interface Permission {
    user: User;
    permission_level: 'editor' | 'visualizador';
}
interface Expediente {
    areas: Area[];
}
interface Oficio {
  expediente: Expediente | null;
  permissions: Permission[];
}

const props = defineProps<{
  oficio: Oficio;
}>();
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-4">Asignaciones y Permisos</h3>
        <div class="space-y-4">
            <!-- Sección de Áreas -->
            <div>
                <h4 class="flex items-center text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
                    <Building class="w-4 h-4 mr-2" />
                    Áreas Involucradas
                </h4>
                <ul v-if="oficio.expediente && oficio.expediente.areas.length > 0" class="space-y-1">
                    <li v-for="area in oficio.expediente.areas" :key="area.id" class="text-sm text-gray-800 dark:text-gray-200">
                        - {{ area.nombre }}
                    </li>
                </ul>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay áreas asignadas.</p>
            </div>

            <!-- Sección de Usuarios -->
            <div class="pt-4 border-t dark:border-gray-700">
                <h4 class="flex items-center text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
                    <Users class="w-4 h-4 mr-2" />
                    Usuarios con Permiso
                </h4>
                <ul v-if="oficio.permissions.length > 0" class="space-y-1">
                    <li v-for="permiso in oficio.permissions" :key="permiso.user.id" class="text-sm flex justify-between">
                        <span class="text-gray-800 dark:text-gray-200">- {{ permiso.user.name }}</span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full" 
                              :class="permiso.permission_level === 'editor' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600'">
                            {{ permiso.permission_level }}
                        </span>
                    </li>
                </ul>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay permisos de usuario específicos.</p>
            </div>
        </div>
    </div>
</template>
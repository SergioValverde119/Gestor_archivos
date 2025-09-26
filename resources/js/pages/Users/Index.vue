<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Edit, Trash2, UserPlus } from 'lucide-vue-next';

// --- Definición de Tipos para los Datos del Controlador ---
interface Area {
    id: number;
    nombre: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  cargo: string | null;
  area: Area | null; // El área que jefatura, si aplica
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedUsers {
  data: User[];
  links: PaginationLink[];
}

const props = defineProps<{
  users: PaginatedUsers;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Usuarios', href: '/users' },
];

</script>

<template>
  <Head title="Gestión de Usuarios" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Usuarios</h1>
        <Link 
          href="/users/create" 
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700"
        >
          <UserPlus class="w-4 h-4 mr-2" />
          Crear Usuario
        </Link>
      </div>

      <!-- Tabla de Usuarios -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Rol del Sistema</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Cargo / Puesto</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Área de Jefatura</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="users.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                  No hay usuarios registrados.
                </td>
              </tr>
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ user.role.replace('_', ' ') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.cargo || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.area?.nombre || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-4">
                    <Link :href="`/users/${user.id}/edit`" class="text-indigo-600 hover:text-indigo-800" title="Editar">
                      <Edit class="w-5 h-5" />
                    </Link>
                    <Link :href="`/users/${user.id}`" method="delete" as="button" class="text-red-600 hover:text-red-800" title="Eliminar" :onBefore="() => confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                      <Trash2 class="w-5 h-5" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación -->
        <div v-if="users.links.length > 3" class="flex justify-center mt-6">
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <Link
              v-for="(link, key) in users.links"
              :key="key"
              :href="link.url ?? '#'"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              :class="{
                'bg-blue-600 text-white border-blue-600': link.active,
                'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active && link.url,
                'opacity-50 pointer-events-none bg-gray-100 dark:bg-gray-900': !link.url,
              }"
              v-html="link.label"
            />
          </nav>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
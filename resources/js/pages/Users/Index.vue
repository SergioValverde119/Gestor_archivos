<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Edit, Trash2, UserPlus, Search } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';

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
  area: Area | null;
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
  areas: Area[];
  filters: {
    search?: string;
    role?: string;
    area_id?: string;
  }
}>();

// --- Lógica para filtros y búsqueda ---
const filters = ref({
    search: props.filters.search || '',
    role: props.filters.role || null,
    area_id: props.filters.area_id || null,
});

watch(filters, debounce(() => {
    router.get('/users', filters.value, {
        preserveState: true,
        replace: true,
    });
}, 300), { deep: true });


// --- Lógica para el modal de confirmación ---
const userToDelete = ref<User | null>(null);

const confirmDeletion = (user: User) => {
    userToDelete.value = user;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(`/users/${userToDelete.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                userToDelete.value = null;
            },
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Usuarios', href: '/users' },
];

const flash = computed(() => usePage().props.flash as { success?: string; error?: string });

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
      
      <div v-if="flash && flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ flash.success }}</span>
      </div>
      <div v-if="flash && flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ flash.error }}</span>
      </div>

      <!-- Barra de Búsqueda y Filtros -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Filtro por Rol -->
            <select v-model="filters.role" class="block w-full rounded-md shadow-sm">
                <option :value="null">Todos los Roles</option>
                <option value="admin">Admin</option>
                <option value="director">Director</option>
                <option value="jefe_area">Jefe de Área</option>
                <option value="operativo">Operativo</option>
            </select>
            <!-- Filtro por Área -->
            <select v-model="filters.area_id" class="block w-full rounded-md shadow-sm">
                <option :value="null">Todas las Áreas</option>
                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
            </select>
            <!-- Búsqueda por texto -->
            <div class="relative">
                <input
                    type="text"
                    v-model="filters.search"
                    placeholder="Buscar por nombre o email..."
                    class="w-full rounded-md shadow-sm pl-10"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <Search class="h-5 w-5 text-gray-400" />
                </div>
            </div>
        </div>
      </div>


      <!-- Tabla de Usuarios -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Rol</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Cargo</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Área</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="users.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                  No hay usuarios que coincidan con los filtros.
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
                    <button @click="confirmDeletion(user)" class="text-red-600 hover:text-red-800" title="Eliminar">
                      <Trash2 class="w-5 h-5" />
                    </button>
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

    <!-- Modal de Confirmación para Eliminar -->
    <div v-if="userToDelete" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex justify-center items-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-sm">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Confirmar Eliminación</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                ¿Estás seguro de que deseas eliminar al usuario "<strong>{{ userToDelete.name }}</strong>"? Esta acción no se puede deshacer.
            </p>
            <div class="flex justify-end space-x-2">
                <button @click="userToDelete = null" class="px-4 py-2 text-sm font-medium rounded-md">Cancelar</button>
                <button @click="deleteUser" class="px-4 py-2 text-sm font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
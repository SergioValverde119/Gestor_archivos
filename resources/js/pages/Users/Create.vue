<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// --- Definición de Tipos para las Props ---
interface Area {
  id: number;
  nombre: string;
}

const props = defineProps<{
  areas: Area[];
  flash: { success?: string; }
}>();

// --- Lógica de Éxito ---
// El estado de éxito se basa en el mensaje 'flash' que envía el controlador
const submissionSuccessful = ref(!!props.flash?.success);

// --- Lógica del Formulario ---
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  cargo: '',
  role: 'operativo', // Rol por defecto
  area_id: null as number | null,
});

const submit = () => {
  // Al tener éxito, el controlador redirigirá de vuelta a esta página
  // y enviará el mensaje 'flash', lo que activará el panel de éxito.
  form.post('/users');
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Usuarios', href: '/users' },
  { title: 'Crear', href: '/users/create' },
];
</script>

<template>
  <Head title="Crear Usuario" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Crear Nuevo Usuario</h1>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-2xl mx-auto">
        
        <!-- Panel de Éxito (se muestra cuando `submissionSuccessful` es true) -->
        <div v-if="submissionSuccessful" class="text-center py-10">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">¡Éxito!</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">El usuario ha sido creado correctamente.</p>
            <div class="mt-6 flex justify-center space-x-4">
                <Link
                    href="/users"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
                >
                    Volver a la Lista
                </Link>
                <Link
                    href="/users/create"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    Crear Otro Usuario
                </Link>
            </div>
        </div>

        <!-- Formulario (se oculta cuando hay éxito) -->
        <form v-else @submit.prevent="submit" class="space-y-6">
          <!-- Nombre -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo</label>
            <input type="text" id="name" v-model="form.name" required class="mt-1 block w-full rounded-md shadow-sm" />
            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
            <input type="email" id="email" v-model="form.email" required class="mt-1 block w-full rounded-md shadow-sm" />
            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
          </div>

          <!-- Contraseña -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
              <input type="password" id="password" v-model="form.password" required class="mt-1 block w-full rounded-md shadow-sm" />
              <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Contraseña</label>
              <input type="password" id="password_confirmation" v-model="form.password_confirmation" required class="mt-1 block w-full rounded-md shadow-sm" />
            </div>
          </div>

           <!-- Cargo / Puesto -->
          <div>
            <label for="cargo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo / Puesto</label>
            <input type="text" id="cargo" v-model="form.cargo" class="mt-1 block w-full rounded-md shadow-sm" />
            <div v-if="form.errors.cargo" class="text-red-500 text-sm mt-1">{{ form.errors.cargo }}</div>
          </div>

          <!-- Rol del Sistema -->
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rol en el Sistema</label>
            <select id="role" v-model="form.role" required class="mt-1 block w-full rounded-md shadow-sm">
              <option value="operativo">Operativo</option>
              <option value="jefe_area">Jefe de Área</option>
              <option value="director">Director</option>
              <option value="admin">Administrador</option>
            </select>
            <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
          </div>

          <!-- Área de Pertenencia (condicional) -->
          <div v-if="form.role === 'jefe_area' || form.role === 'operativo'">
            <label for="area_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Área de Pertenencia</label>
            <select id="area_id" v-model="form.area_id" required class="mt-1 block w-full rounded-md shadow-sm">
              <option :value="null" disabled>Seleccione un área</option>
              <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
            </select>
            <div v-if="form.errors.area_id" class="text-red-500 text-sm mt-1">{{ form.errors.area_id }}</div>
          </div>

          <!-- Botones de Acción -->
          <div class="mt-6 flex justify-end space-x-4">
            <Link href="/users" class="px-4 py-2 rounded-md text-sm font-medium">Cancelar</Link>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors"
              :disabled="form.processing"
            >
              Crear Usuario
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
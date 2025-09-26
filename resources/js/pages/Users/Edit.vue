<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// --- Definición de Tipos ---
interface Area {
  id: number;
  nombre: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  cargo: string | null;
  role: 'admin' | 'director' | 'jefe_area' | 'operativo';
  area_id: number | null;
}

const props = defineProps<{
  user: User;
  areas: Area[];
}>();

// --- Lógica del Formulario ---
const form = useForm({
  name: props.user.name,
  email: props.user.email,
  cargo: props.user.cargo,
  role: props.user.role,
  area_id: props.user.area_id,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  // Se usa el método PUT para actualizar el recurso
  form.put(`/users/${props.user.id}`);
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Usuarios', href: '/users' },
  { title: props.user.name, href: `/users/${props.user.id}` }, // Asumiendo que tienes una ruta 'show'
  { title: 'Editar', href: `/users/${props.user.id}/edit` },
];
</script>

<template>
  <Head :title="`Editar Usuario: ${user.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Editar Usuario</h1>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-2xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">

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
          <div class="border-t dark:border-gray-700 pt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Deja los siguientes campos en blanco si no deseas cambiar la contraseña.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Contraseña</label>
                <input type="password" id="password" v-model="form.password" class="mt-1 block w-full rounded-md shadow-sm" />
                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
              </div>
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Nueva Contraseña</label>
                <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="mt-1 block w-full rounded-md shadow-sm" />
              </div>
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
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
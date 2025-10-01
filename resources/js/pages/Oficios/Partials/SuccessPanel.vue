<script setup lang="ts">
const props = defineProps<{
  title: string;
  message: string;
}>();
</script>

<template>
    <div class="text-center py-10">
        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">{{ title }}</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ message }}</p>
        <div class="mt-6 flex justify-center space-x-4">
            <!-- Los botones se insertarán aquí -->
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="flex items-center gap-2">

        <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
        </div>
        
        <div v-if="!isCollapsed" class="grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">Seduvi RH</span>
        </div>
    </div>
</template>
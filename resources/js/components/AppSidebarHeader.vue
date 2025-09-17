<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import Icon from '@/components/Icon.vue';
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center space-x-2">
            <span class="text-xl font-bold">@</span>
            <span class="font-bold text-gray-800">Seduvi RH</span>
        </div>

        <div class="flex-grow"></div>

        <div class="flex items-center space-x-6">
            <div class="relative flex items-center cursor-pointer">
                <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs h-4 w-4 flex items-center justify-center -mt-1 -mr-1">2</span>
                <Icon name="bell" class="text-gray-600 text-lg" />
            </div>

            <NavUser />
        </div>
    </header>
</template>
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { area } from '@/routes'; 
import { LoaderCircle, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    areas: { id: number; nombre: string }[];
}>();

const form = useForm({
    nombre: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Áreas',
        href: area.index().url,
    },
];

const areaToDelete = ref<{ id: number; nombre: string } | null>(null);






</script>

<template>
    <Head title="Áreas" />

    <AppLayout :breadcrumbs="breadcrumbs">
       

        <div class="py-12">
            hola
        </div>
    </AppLayout>
</template>
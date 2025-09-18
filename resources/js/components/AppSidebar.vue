<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, CalendarDays, LayoutGrid, LayoutDashboard, FilePlus2, House, Search, Building2  } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Obtén la función para alternar y el estado de la barra lateral
const { toggleSidebar, state } = useSidebar();

// Las rutas ya no se generan con "wayfinder", se han reemplazado por URLs fijas.
const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: '/',
        icon: House,
    },
    {
        title: 'Busqueda',
        href: '/oficios',
        icon: Search,
    },
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Subir Oficio',
        href: '/oficios/create', // He ajustado la ruta para que sea más específica
        icon: FilePlus2,
    },
    {
        title: 'Areas',
        href: '/areas',
        icon: Building2,
    },
];

const footerNavItems: NavItem[] = [
    /*{
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },*/
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg">
                        <div @click="toggleSidebar()">
                            <AppLogo :is-collapsed="state === 'collapsed'" />
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div> <NavFooter :items="footerNavItems" />
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

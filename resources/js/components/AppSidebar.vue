<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, FilePlus2, House, Search, Building2, FolderArchive, FileUp, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Obtén la función para alternar y el estado de la barra lateral
const { toggleSidebar, state } = useSidebar();

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: '/',
        icon: House,
    },
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Expedientes',
        href: '/expedientes',
        icon: FolderArchive,
    },
    {
        title: 'Buscar Oficios',
        href: '/oficios',
        icon: Search,
    },
    {
        title: 'Registrar Entrada',
        href: '/oficios/entrada/registrar',
        icon: FilePlus2,
    },
    {
        title: 'Generar Salida',
        href: '/oficios/salida/crear',
        icon: FileUp,
    },
    {
        title: 'Áreas',
        href: '/areas',
        icon: Building2,
    },
    {
        title: 'Usuarios',
        href: '/users',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    // ...
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
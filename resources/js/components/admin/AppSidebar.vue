<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, ClipboardList, Home, Hotel, Calendar } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '../AppLogo.vue';

// Get auth data from the page props
const page = usePage();
const user = computed(() => page.props.auth.user);
const can = computed(() => page.props.auth.can || {});

// Core navigation items everyone can see
const dashboardItem = {
    title: 'Dashboard',
    href: '/admin/dashboard',
    icon: LayoutGrid,
};

// Build navigation based on permissions
const mainNavItems = computed(() => {
    const items: NavItem[] = [dashboardItem];

    // Only add Manage Managers link if user has the 'view_managers' permission
    if (can.value.manage_managers) {
        items.push({
            title: 'Manage Managers',
            href: '/admin/managers',
            icon: Users,
        });
    }

    // Add other menu items based on permissions
    if (can.value.view_receptionists || can.value.manage_receptionists) {
        items.push({
            title: 'Manage Receptionists',
            href: '/admin/receptionists',
            icon: Users,
        });
    }

    if (can.value.view_clients || can.value.manage_clients) {
        items.push({
            title: 'Manage Pending Clients',
            href: '/dashboard/receptionist/clients/pending',
            icon: Users,
        });
        items.push({
            title: 'My Approved Clients',
            href: '/dashboard/receptionist/clients/approved',
            icon: Users,
        });
        items.push({
            title: 'My Clients Reservations',
            href: '/dashboard/receptionist/clients/reservations',
            icon: Hotel,
        });

    }

    if (can.value.view_floors || can.value.manage_floors) {
        items.push({
            title: 'Manage Floors',
            href: '/admin/floors',
            icon: Hotel,
        });
    }

    if (can.value.view_rooms || can.value.manage_rooms) {
        items.push({
            title: 'Manage Rooms',
            href: '/admin/rooms',
            icon: Home,
        });
    }

    if (can.value.view_reservations || can.value.view_client_reservations) {
        items.push({
            title: 'Reservations',
            href: '/admin/reservations',
            icon: Calendar,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

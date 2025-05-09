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
let roleRoute = '';
if (user.value.role === 'manager') {
    roleRoute = "/manager";
}
else if (user.value.role === 'admin') {
    roleRoute = "/admin";
}
const role = computed(() => user.value?.role); // Get user role

// Core navigation items everyone can see

const dashboardItem = {
    title: 'Dashboard',
    href: (role.value == "admin") ? '/admin/dashboard' : '/manager/dashboard',
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
    if (can.value.manage_receptionists) {

        if (role.value == "manager") {
            items.push({
                title: 'Manage Receptionists',
                href: '/manager/receptionists',
                icon: Users,
            });
        }

        else {
            items.push({
                title: 'Manage Receptionists',
                href: '/admin/receptionists',
                icon: Users,
            });
        }

    }

    if ((can.value.approve_client || can.value.view_approved_clients || can.value.view_client_reservations) || can.value.manage_clients) {
        if(user.value.role === 'manager' || user.value.role === 'admin'){
            items.push({
            title: 'Manage Clients',
            href: `${roleRoute}/clients`,
            icon: Users,
        });
        }
        items.push({
            title: 'Manage Pending Clients',
            href: '/dashboard/receptionist/clients/pending',
            icon: Users,
        });
    }

    if (can.value.manage_floors) {

        if (role.value == "manager") {
            items.push({
                title: 'Manage Floors',
                href: '/manager/floors',
                icon: Hotel,
            });
        }
        else {
            items.push({
                title: 'Manage Floors',
                href: '/admin/floors',
                icon: Hotel,
            });
        }
    }

    if (can.value.manage_rooms) {
        items.push({
            title: 'Manage Rooms',
            href: `${roleRoute}/rooms`,
            icon: Home,
        });
    }

    if (can.value.manage_reservations) {
        items.push({
            title: 'Reservations',
            href: '/admin/clients/reservations',
            icon: Calendar,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
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

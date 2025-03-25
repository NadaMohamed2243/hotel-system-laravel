<script setup lang="ts">
// import AppLayout from '@/layouts/ReceptionistLayout.vue';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, h } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable.vue'; // Import the DataTable component

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Client {
    id: number;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    mobile: string | null;
    country: string | null;
    gender: 'male' | 'female' | null;
}

const props = defineProps<{
    clients: Client[];
    pagination: {
        page: number;
        pageSize: number;
        total: number;
    };
}>();

const clients = computed(() => props.clients);

const columns = [
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'text-left' }, 'Client Name'),
        cell: ({ row }) => {
            const name = row.original.user?.name || 'N/A';
            return h('div', { class: 'text-left font-medium' }, name);
        },
    },
    {
        accessorKey: 'email',
        header: () => h('div', { class: 'text-left' }, 'Email'),
        cell: ({ row }) => {
            const email = row.original.user?.email || 'N/A';
            return h('div', { class: 'text-left font-medium' }, email);
        },
    },
    {
        accessorKey: 'mobile',
        header: () => h('div', { class: 'text-left' }, 'Mobile'),
        cell: ({ row }) => {
            const mobile = row.original.mobile || 'N/A';
            return h('div', { class: 'text-left font-medium' }, mobile);
        },
    },
    {
        accessorKey: 'country',
        header: () => h('div', { class: 'text-left' }, 'Country'),
        cell: ({ row }) => {
            const country = row.original.country || 'N/A';
            return h('div', { class: 'text-left font-medium' }, country);
        },
    },
    {
        accessorKey: 'gender',
        header: () => h('div', { class: 'text-left' }, 'Gender'),
        cell: ({ row }) => {
            const gender = row.original.gender || 'male';
            return h('div', { class: 'text-left font-medium' }, gender);
        },
    },
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/dashboard/receptionist',
    },
    {
        title: 'Approved Clients',
        href: '/dashboard/receptionist/clients/approved',
    },
];
</script>

<template>
    <div>

        <Head title="My Approved Clients" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="space-y-4">
                <h1 class="m-4 text-2xl font-bold text-gray-800 dark:text-gray-200">My Approved Clients</h1>
                <DataTable :data="clients" :columns="columns" :pagination="props.pagination" :manual-pagination="true"
                    @pagination-change="(newPagination) => {
                        router.get(route('receptionist.approvedClients'), {
                            page: newPagination.pageIndex + 1,
                            pageSize: newPagination.pageSize,
                        });
                    }" />
            </div>
        </AppLayout>
    </div>
</template>

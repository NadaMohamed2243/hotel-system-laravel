<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ClientSidebar from '@/components/ClientSidebar.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex">
            <!-- Client Sidebar -->
            <ClientSidebar />

            <!-- dashboard content-->
            <div class="flex-1 p-6">
                <h1 class="text-2xl font-semibold mb-4">Welcome to Your Dashboard</h1>
                <p class="text-gray-600">Here you can  make & manage your reservations.</p>
            </div>
        </div>
    </AppLayout>
</template>

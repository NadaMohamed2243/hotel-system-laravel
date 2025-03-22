<script setup lang="ts">
import AppLayout from '@/layouts/ReceptionistLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/dashboard/receptionist',
    },
];
</script>

<template>
    <div>
        <Head title="Receptionist Dashboard" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                approve client
            </div>
        </AppLayout>
    </div>
</template>

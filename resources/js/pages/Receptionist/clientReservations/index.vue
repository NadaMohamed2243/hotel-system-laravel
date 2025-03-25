<script setup lang="ts">
// import AppLayout from '@/layouts/ReceptionistLayout.vue';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    reservations: any; // Adjust type based on your actual data structure
}>();

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
                client reservations  {{ props.reservations }}
            </div>
        </AppLayout>
    </div>
</template>

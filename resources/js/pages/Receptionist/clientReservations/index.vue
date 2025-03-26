<script setup lang="ts">
// import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Head } from '@inertiajs/vue3';
// import { type BreadcrumbItem } from '@/types';
import { ref, computed, h } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable.vue';
import ClientManagement from '@/layouts/ManageClients.vue';

defineOptions({
    layout: ClientManagement,
});
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
}

export interface Room {
  id: number;
  number: string;
}

export interface Reservation {
  id: number;
  client: Client;
  room: Room;
  accompany_number: number;
  paid_price: number;  // stored in cents
}

const props = defineProps<{
  reservations: Reservation[];
  pagination: {
    page: number;
    pageSize: number;
    total: number;
  };
}>();

// const reservations = computed(() => props.reservations);

const columns = [
  {
    accessorKey: 'client.user.name',
    header: () => h('div', { class: 'text-left' }, 'Client Name'),
    cell: ({ row }) => {
      const name = row.original.client?.user?.name || 'N/A';
      return h('div', { class: 'text-left font-medium' }, name);
    },
  },
  {
    accessorKey: 'accompany_number',
    header: () => h('div', { class: 'text-left' }, 'Accompany Number'),
    cell: ({ row }) => {
      const number = row.original.accompany_number ?? 0;
      return h('div', { class: 'text-left font-medium' }, number);
    },
  },
  {
    accessorKey: 'room.number',
    header: () => h('div', { class: 'text-left' }, 'Room Number'),
    cell: ({ row }) => {
      const roomNumber = row.original.room?.number || 'N/A';
      return h('div', { class: 'text-left font-medium' }, roomNumber);
    },
  },
  {
    // accessorKey: 'paid_price',
    // header: () => h('div', { class: 'text-left' }, 'Paid Price'),
    // cell: ({ row }) => {
    //   const price = row.original.paid_price ? `$${row.original.paid_price.toFixed(2)}` : '$0.00';
    //   return h('div', { class: 'text-left font-medium' }, price);
    // },
    accessorKey: 'paid_price',
        header: () => h('div', { class: 'text-left' }, 'Paid Price ($)'),
        cell: ({ row }) => {
            // Convert cents to dollars and format with 2 decimal places
            const priceInDollars = (row.original.paid_price / 100).toFixed(2);
            return h('div', { class: 'text-left font-medium' }, `$${priceInDollars}`);
        },
  },
];

// const breadcrumbs: BreadcrumbItem[] = [
//   {
//     title: 'Receptionist Dashboard',
//     href: '/dashboard/receptionist',
//   },
//   {
//     title: 'Client Reservations',
//     href: '/dashboard/receptionist/client-reservations',
//   },
// ];
</script>

<template>
  <div>
    <Head title="Client Reservations" />
    <!-- <AppLayout :breadcrumbs="breadcrumbs"> -->
      <div class="space-y-4">
      <h1 class="m-4 text-2xl font-bold text-gray-800 dark:text-gray-200">Client Reservations</h1>
      <DataTable
        :data="props.reservations"
        :columns="columns"
        :pagination="props.pagination"
        :manual-pagination="true"
        @pagination-change="(newPagination) => {
          router.get(route('receptionist.clientReservations'), {
            page: newPagination.pageIndex + 1,
            pageSize: newPagination.pageSize,
          });
        }"
      />
    </div>
    <!-- </AppLayout> -->
  </div>
</template>

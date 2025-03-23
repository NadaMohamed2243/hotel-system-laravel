<script setup lang="ts">
import AppLayout from '@/layouts/ReceptionistLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, h } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable.vue'; // Import the DataTable component
import { Button } from '@/components/ui/button';

export interface Client {
  id: number;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  mobile: string | null;
  country: string | null;
  gender: 'male' | 'female' | 'other' | null;
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
      const gender = row.original.gender || 'N/A';
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
    title: 'Pending Clients',
    href: '/dashboard/receptionist/pending-clients',
  },
];

// const approveClient = (client: Client) => {
//   router.patch(`/dashboard/receptionist/clients/${client.id}`);
//   router.reload();
// };

// const unapproveClient = (client: Client) => {
//   router.delete(`/dashboard/receptionist/clients/delete/${client.id}`);
//   router.reload();
// };

const approveClient = async (client: Client) => {
  await router.patch(`/dashboard/receptionist/clients/${client.id}`);
  router.visit(route('receptionist.pendingClients')); // Navigate to the same page
};

const unapproveClient = async (client: Client) => {
  await router.delete(`/dashboard/receptionist/clients/delete/${client.id}`);
  router.visit(route('receptionist.pendingClients')); // Navigate to the same page
};
</script>

<template>
  <div>
    <Head title="Pending Clients" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <DataTable
        :data="clients"
        :columns="columns"
        :pagination="props.pagination"
        :manual-pagination="true"
        @pagination-change="(newPagination) => {
          router.get(route('receptionist.pendingClients'), {
            page: newPagination.pageIndex + 1,
            pageSize: newPagination.pageSize,
          });
        }"
      >
        <!-- Action Buttons Slot -->
        <template #actions="{ row }">
          <div class="flex justify-center gap-2">
            <Button
              variant="outline"
              size="sm"
              @click="approveClient(row.original)"
              class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-700 mr-2"
            >
              Approve
            </Button>
            <Button
              variant="outline"
              size="sm"
              @click="unapproveClient(row.original)"
              class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700"
            >
              Unapprove
            </Button>
          </div>
        </template>
      </DataTable>
    </AppLayout>
  </div>
</template>

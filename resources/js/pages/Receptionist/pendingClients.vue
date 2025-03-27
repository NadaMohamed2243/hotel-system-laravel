<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import ClientManagement from '@/layouts/ManageClients.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineOptions({
    layout: ClientManagement,
});

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

const showDeleteDialog = ref(false);
const clientToDelete = ref<Client | null>(null);
const isDeleting = ref(false);

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

const approveClient = async (client: Client) => {
    router.patch(`/dashboard/receptionist/clients/${client.id}`);

    const itemsOnCurrentPage = clients.value.length;
    const currentPage = props.pagination.page;

    if (itemsOnCurrentPage === 1 && currentPage > 1) {
        router.get(route('receptionist.pendingClients'), {
            page: currentPage - 1,
            pageSize: props.pagination.pageSize,
        });
    } else {
        router.get(route('receptionist.pendingClients'), {
            page: currentPage,
            pageSize: props.pagination.pageSize,
        });
    }
};

const confirmDelete = (client: Client) => {
    clientToDelete.value = client;
    showDeleteDialog.value = true;
};

const unapproveClient = async () => {
    if (!clientToDelete.value) return;

    isDeleting.value = true;

    try {
        await router.delete(`/dashboard/receptionist/clients/delete/${clientToDelete.value.id}`, {
            onSuccess: () => {
                // Calculate if the current page will be empty after deletion
                const itemsOnCurrentPage = clients.value.length;
                const currentPage = props.pagination.page;

                // If this was the last item on the current page and we're not on page 1
                if (itemsOnCurrentPage === 1 && currentPage > 1) {
                    router.get(route('receptionist.pendingClients'), {
                        page: currentPage - 1, // Go to previous page
                        pageSize: props.pagination.pageSize,
                    });
                } else {
                    // Otherwise stay on the same page
                    router.get(route('receptionist.pendingClients'), {
                        page: currentPage,
                        pageSize: props.pagination.pageSize,
                    });
                }
            },
            onError: (errors) => {
                console.error('Error deleting client:', errors);
                // You might want to show an error message to the user here
            },
            onFinish: () => {
                showDeleteDialog.value = false;
                isDeleting.value = false;
            }
        });
    } catch (error) {
        console.error('Error deleting client:', error);
        isDeleting.value = false;
    }
};
</script>

<template>
    <div>
        <Head title="My Pending Clients" />
        <div class="space-y-4">
            <h1 class="m-4 text-2xl font-bold text-gray-800 dark:text-gray-200">My Pending Clients</h1>
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
                            @click="confirmDelete(row.original)"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700"
                        >
                            Unapprove
                        </Button>
                    </div>
                </template>
            </DataTable>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="showDeleteDialog">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Confirm Deletion</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to unapprove this client? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="py-4">
                        <p class="text-sm text-gray-600">
                            You're about to unapprove <span class="font-semibold">{{ clientToDelete?.user?.name || 'N/A' }}</span>.
                            This will prevent them from accessing the system.
                        </p>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showDeleteDialog = false"
                            :disabled="isDeleting"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            class="bg-red-500 hover:bg-red-700"
                            @click="unapproveClient"
                            :disabled="isDeleting"
                        >
                            <span v-if="isDeleting">Processing...</span>
                            <span v-else>Confirm Unapprove</span>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>

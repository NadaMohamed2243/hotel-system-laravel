<script setup lang="ts">
import AppLayout from '@/layouts/ReceptionistLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { valueUpdater } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { ChevronDown } from 'lucide-vue-next';
import { ref, h, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
// const pagination = ref({
//   pageIndex: 0, // Start from the first page
//   pageSize: 8, // Render 8 rows per page
// });

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
    gender: 'male' | 'female' | 'other' | null;
}

// const { clients } = defineProps<{ clients: Client[];
//  }>();

const props = defineProps<{
    clients: Client[];
    pagination: {
        page: number;
        pageSize: number;
        total: number;
    };
}>();
const clients = computed(() => props.clients);


const pagination = ref({
    pageIndex: props.pagination.page - 1, // Convert to zero-based index
    pageSize: props.pagination.pageSize,
});


watch(pagination, (newPagination) => {
    try {
        const lastPageIndex = Math.ceil(props.pagination.total / newPagination.pageSize) - 1;
        if (newPagination.pageIndex > lastPageIndex) {
            console.warn('Invalid pageIndex. Resetting to last page.');
            newPagination.pageIndex = lastPageIndex;
        }

        console.log('New pagination state:', newPagination);
        router.get(route('receptionist.approvedClients'), { // Use `route` directly
            page: newPagination.pageIndex + 1, // Convert back to one-based index
            pageSize: newPagination.pageSize,
        });
    } catch (error) {
        console.error('Error during router.get:', error);
    }
}, { deep: true });

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

const columns = [
    {
        accessorKey: 'name', // Flattened key
        header: () => h('div', { class: 'text-left' }, 'Client Name'),
        cell: ({ row }) => {
            const name = row.original.user?.name || 'N/A';
            return h('div', { class: 'text-left font-medium' }, name);
        },
    },
    {
        accessorKey: 'email', // Flattened key
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

const sorting = ref([]);
const columnFilters = ref([]);
const columnVisibility = ref({});
const rowSelection = ref({});
const expanded = ref({});

const table = useVueTable({
    data: clients,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    manualPagination: true, // Enable manual pagination
    pageCount: Math.ceil(props.pagination.total / pagination.value.pageSize),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) => valueUpdater(updaterOrValue, expanded),
    onPaginationChange: (updaterOrValue) => valueUpdater(updaterOrValue, pagination),
    state: {
        get sorting() { return sorting.value; },
        get columnFilters() { return columnFilters.value; },
        get columnVisibility() { return columnVisibility.value; },
        get rowSelection() { return rowSelection.value; },
        get expanded() { return expanded.value; },
        get pagination() {
            return pagination.value;
        },

    },
});

</script>

<template>
    <div>

        <Head title="Receptionist Dashboard" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="w-90 m-4">
                <div class="flex items-center py-4">
                    <Input class="max-w-sm" placeholder="Filter emails..."
                        :model-value="table.getColumn('email')?.getFilterValue() as string"
                        @update:model-value="table.getColumn('email')?.setFilterValue($event)" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="ml-auto">
                                Columns {{ clients.length }}
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuCheckboxItem
                                v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                                :key="column.id" class="capitalize" :model-value="column.getIsVisible()"
                                @update:model-value="(value) => column.toggleVisibility(!!value)">
                                {{ column.id }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                        :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                    :data-state="row.getIsSelected() ? 'selected' : undefined">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-end space-x-2 py-4">
                    <div class="flex-1 text-sm text-muted-foreground">
                        {{ table.getFilteredSelectedRowModel().rows.length }} of
                        {{ table.getFilteredRowModel().rows.length }} row(s) selected.
                    </div>
                    <div class="space-x-2">
                        <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()">
                            Previous
                        </Button>
                        <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()">
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    </div>
</template>

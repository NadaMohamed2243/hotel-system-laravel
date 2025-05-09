<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { Input } from '@/components/ui/input';
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ChevronDown } from 'lucide-vue-next';
import { valueUpdater } from '@/lib/utils';
// Define the props for the DataTable component
const props = defineProps<{
    data: any[]; // The data to display in the table
    columns: any[]; // The column definitionss
    pagination?: {
        page: number;
        pageSize: number;
        total: number;
    }; // Optional pagination props
    manualPagination?: boolean; // Whether to use manual pagination
}>();

// Reactive state for pagination
const pagination = ref({
    pageIndex: props.pagination?.page ? props.pagination.page - 1 : 0, // Convert to zero-based index
    pageSize: props.pagination?.pageSize || 10, // Default to 10 rows per page
});

// Reactive state for sorting, filtering, etc.
const sorting = ref([]);
const columnFilters = ref([]);
const columnVisibility = ref({});
const rowSelection = ref({});
const expanded = ref({});

// Create the table instance
const table = useVueTable({
    data: props.data,
    columns: props.columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    manualPagination: props.manualPagination || false, // Use manual pagination if specified
    pageCount: props.pagination ? Math.ceil(props.pagination.total / pagination.value.pageSize) : 1, // Calculate total pages
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
        get pagination() { return pagination.value; },
    },
});

// Watch for pagination changes (if manual pagination is enabled)
watch(pagination, (newPagination) => {
    if (props.manualPagination) {
        // Emit an event to notify the parent component of pagination changes
        emit('pagination-change', newPagination);
    }
}, { deep: true });


// Emit events for parent component interaction
const emit = defineEmits(['pagination-change']);
</script>

<template>
    <div class="w-90 m-4">
        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()" />
                        </TableHead>
                        <TableHead v-if="$slots.actions" class="text-center">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>

                            <!-- Action Buttons Slot -->
                            <TableCell v-if="$slots.actions" class="text-center">
                                <slot name="actions" :row="row"></slot>
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


        <!-- Enhanced Pagination Controls -->
        <div class="flex items-center justify-between py-4">
            <!-- Page Info -->
            <div class="text-sm text-muted-foreground mx-2">
                Page {{ table.getState().pagination.pageIndex + 1 }} of {{ table.getPageCount() }}
                <span class="mx-2">•</span>
                Total {{ props.pagination?.total || props.data.length }} items
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center justify-end space-x-2 py-4">
                <div class="space-x-2">
                    <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()">
                        Previous
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

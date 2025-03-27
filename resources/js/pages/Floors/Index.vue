<template>
    <AppLayout>
      <div class="flex flex-col gap-4 p-8">
        <!-- Dialog Components -->
        <AddFloorDialog 
          ref="addFloorDialog" 
          :managers="managers" 
          @created="handleFloorCreated"
          @updated="handleFloorUpdated"
          @success="handleSuccess"
        />
        <ViewFloorDialog ref="viewDialog" :floor="selectedFloor" />
        <ConfirmationDialog
          ref="confirmationDialog"
          title="Delete Floor"
          message="Are you sure you want to delete this floor? This action cannot be undone."
          :floorName="floorToDelete?.name || ''"
          :floorNumber="floorToDelete?.number || ''"
          :floorManagerName="floorToDelete?.manager_name || ''"
          @confirm="confirmDelete"
          @cancel="cancelDelete"
        />
  
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Floors</h1>
          <Button @click="openAddDialog" v-if="canCreate">
            Add Floor
          </Button>
        </div>
  
        <!-- Main Content Card -->
        <Card class="w-full">
          <CardContent class="p-0">
            <!-- Table -->
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Number</TableHead>
                  <TableHead>Created At</TableHead>
                  <TableHead>Manager</TableHead>
                  <TableHead class="text-center">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="floor in floors.data" :key="floor.id">
                  <TableCell>{{ floor.name }}</TableCell>
                  <TableCell>{{ floor.number }}</TableCell>
                  <TableCell>{{ floor.created_at || 'N/A' }}</TableCell>
                  <TableCell>
                    {{ floor.manager_name || 'N/A' }}
                    <span v-if="floor.is_own_floor" class="ml-2 text-xs text-green-500">(Yours)</span>
                  </TableCell>
                  <TableCell class="text-center">
                    <div class="flex justify-center gap-2">
                      <Button variant="outline" size="sm" @click="viewFloor(floor)">
                        <Eye class="h-4 w-4 mr-1" />
                        View
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        @click="openEditDialog(floor)"
                        :disabled="!floor.can_edit"
                      >
                        <Pencil class="h-4 w-4 mr-1" />
                        Edit
                      </Button>
                      <Button
                        variant="destructive"
                        size="sm"
                        @click="openDeleteDialog(floor)"
                        :disabled="!floor.can_delete"
                      >
                        <Trash class="h-4 w-4 mr-1" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="floors.data.length === 0">
                  <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                    No floors found
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <!-- Pagination -->
            <div class="flex items-center justify-between px-4 py-4 border-t">
              <!-- Pagination Info -->
              <div>
                <p class="text-sm text-gray-700">
                  Showing 
                  <span class="font-medium">{{ floors.meta.from || 0 }}</span> 
                  to 
                  <span class="font-medium">{{ floors.meta.to || 0 }}</span> 
                  of 
                  <span class="font-medium">{{ floors.meta.total }}</span> 
                  results
                </p>
              </div>
              
              <!-- Pagination Controls -->
              <div class="flex items-center space-x-2">
                <!-- Page Size Selector -->
                <div class="flex items-center space-x-2 mr-4">
                  <label for="pageSize" class="text-sm">Per page:</label>
                  <select 
                    id="pageSize" 
                    v-model="pagination.pageSize" 
                    @change="handlePaginationChange"
                    class="border rounded p-1 text-sm"
                  >
                    <option v-for="size in [5, 10, 20, 50]" :key="size" :value="size">
                      {{ size }}
                    </option>
                  </select>
                </div>
                
                <!-- Page Navigation Buttons -->
                <Button 
                  variant="outline" 
                  size="sm" 
                  @click="goToPage(1)" 
                  :disabled="pagination.pageIndex === 0"
                >
                  <ChevronsLeft class="h-4 w-4" />
                </Button>
                
                <Button 
                  variant="outline" 
                  size="sm" 
                  @click="goToPage(pagination.pageIndex)" 
                  :disabled="pagination.pageIndex === 0"
                >
                  <ChevronLeft class="h-4 w-4" />
                </Button>
                
                <!-- Page Numbers -->
                <div class="flex items-center space-x-1">
                  <Button 
                    v-for="p in getPageNumbers()" 
                    :key="p" 
                    :variant="p === pagination.pageIndex + 1 ? 'default' : 'outline'"
                    size="sm"
                    @click="goToPage(p)"
                  >
                    {{ p }}
                  </Button>
                </div>
                
                <Button 
                  variant="outline" 
                  size="sm" 
                  @click="goToPage(pagination.pageIndex + 2)" 
                  :disabled="pagination.pageIndex >= floors.meta.pageCount - 1"
                >
                  <ChevronRight class="h-4 w-4" />
                </Button>
                
                <Button 
                  variant="outline" 
                  size="sm" 
                  @click="goToPage(floors.meta.pageCount)" 
                  :disabled="pagination.pageIndex >= floors.meta.pageCount - 1"
                >
                  <ChevronsRight class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  </template>


<script setup>
import { Eye, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { ref, reactive, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import ConfirmationDialog from '@/components/floors/ConfirmationDialog.vue';
import ViewFloorDialog from '@/components/floors/ViewFloorDialog.vue';
import AddFloorDialog from '@/components/floors/AddFloorDialog.vue';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import { Pencil, Trash } from 'lucide-vue-next';

// Get page props
const page = usePage();
const floors = ref(page.props.floors);
const managers = ref(page.props.managers);
const is_manager_view = ref(page.props.is_manager_view);
const canCreate = ref(page.props.canCreate);

// Pagination state management
const pagination = reactive({
    pageIndex: floors.value.meta.pageIndex, 
    pageSize: floors.value.meta.pageSize
});

// Critical fix: Watch page props directly using watch instead of watchEffect
watch(() => page.props.floors, (newFloors) => {
    floors.value = newFloors;
    pagination.pageIndex = newFloors.meta.pageIndex;
    pagination.pageSize = newFloors.meta.pageSize;
}, { deep: true });

// Dialog references
const addFloorDialog = ref(null);
const viewDialog = ref(null);
const confirmationDialog = ref(null);
const selectedFloor = ref({});
const floorToDelete = ref(null);
const isEditing = ref(false);

// Calculate which page numbers to display
const getPageNumbers = () => {
    const current = pagination.pageIndex + 1; // 1-based for display
    const total = floors.value.meta.pageCount;
    const delta = 1; // Pages to show before/after current
    const pages = [];
    
    // First page
    if (current > 1 + delta) {
        pages.push(1);
        if (current > 2 + delta) {
            pages.push('...');
        }
    }

    // Pages around current
    for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
        pages.push(i);
    }

    // Last page
    if (current < total - delta) {
        if (current < total - 1 - delta) {
            pages.push('...');
        }
        pages.push(total);
    }

    return pages;
};

// Key change: Use router.visit instead of router.get for more reliable updates
const goToPage = (page) => {
    if (page === '...') return;
    
    router.visit(route(route().current()), {
        data: { 
            page: page, 
            pageSize: pagination.pageSize 
        },
        preserveState: true,
        preserveScroll: true,
        only: ['floors']
    });
};

const handlePaginationChange = () => {
    router.visit(route(route().current()), {
        data: { 
            page: 1, // Reset to first page when changing size
            pageSize: pagination.pageSize 
        },
        preserveState: true,
        preserveScroll: true,
        only: ['floors']
    });
};

// CRUD Operations
const handleFloorCreated = (newFloor) => {
    // If we're not on the first page, navigate there
    if (pagination.pageIndex > 0) {
        router.visit(route(route().current()), {
            data: { page: 1, pageSize: pagination.pageSize },
            preserveState: true,
            only: ['floors']
        });
        addFloorDialog.value.close();
        return;
    }
    
    // If on first page, update locally
    floors.value.data = floors.value.data.filter(f => f.id !== newFloor.id);
    floors.value.data.unshift({
        ...newFloor,
        can_edit: true,
        can_delete: true,
        is_own_floor: true
    });
    
    // If we now have more items than pageSize, remove the last one
    if (floors.value.data.length > pagination.pageSize) {
        floors.value.data.pop();
    }
    
    // Update total count
    floors.value.meta.total += 1;
    
    addFloorDialog.value.close();
};

const handleFloorUpdated = (updatedFloor) => {
    // Find if floor exists in current page
    const floorIndex = floors.value.data.findIndex(f => f.id === updatedFloor.id);
    
    if (floorIndex !== -1 || pagination.pageIndex === 0) {
        // Update local data if floor is on current page or we're on first page
        floors.value.data = floors.value.data.filter(f => f.id !== updatedFloor.id);
        floors.value.data.unshift({
            ...updatedFloor,
            can_edit: true,
            can_delete: true,
            is_own_floor: true
        });
        
        // If we exceed page size, remove last item
        if (floors.value.data.length > pagination.pageSize) {
            floors.value.data.pop();
        }
    } else {
        // If we're not on the first page and floor isn't found, go to first page
        router.visit(route(route().current()), {
            data: { page: 1, pageSize: pagination.pageSize },
            preserveState: true,
            only: ['floors']
        });
    }
    
    addFloorDialog.value.close();
};

const handleSuccess = () => {
    // Optional: Add success notification logic
};

// Dialog operations
const openAddDialog = () => {
    isEditing.value = false;
    addFloorDialog.value.open();
};

const viewFloor = (floor) => {
    selectedFloor.value = floor;
    viewDialog.value.open();
};

const openEditDialog = (floor) => {
    if (floor.can_edit) {
        isEditing.value = true;
        addFloorDialog.value.open(floor);
    }
};

const openDeleteDialog = (floor) => {
    if (floor.can_delete) {
        floorToDelete.value = floor;
        confirmationDialog.value.open();
    }
};

const confirmDelete = () => {
    if (floorToDelete.value) {
        const routePrefix = is_manager_view.value ? '/manager' : '/admin';
        router.delete(`${routePrefix}/floors/${floorToDelete.value.id}`, {
            onSuccess: () => {
                // Update local data
                floors.value.data = floors.value.data.filter(f => f.id !== floorToDelete.value.id);
                floors.value.meta.total -= 1;
                
                // Check if page is now empty and we need to go back a page
                if (floors.value.data.length === 0 && pagination.pageIndex > 0) {
                    goToPage(pagination.pageIndex);
                }
                
                floorToDelete.value = null;
            },
            onError: (error) => {
                console.error('Error deleting floor:', error);
            }
        });
    }
};

const cancelDelete = () => {
    floorToDelete.value = null;
};
</script>

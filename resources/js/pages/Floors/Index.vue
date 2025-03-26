<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">
            <!-- Add Dialog -->
            <AddFloorDialog 
      ref="addFloorDialog" 
      :managers="managers" 
      @created="handleFloorCreated"
      @success="handleSuccess"
    />
            <!-- View Dialog -->
            <ViewFloorDialog ref="viewDialog" :floor="selectedFloor" />

            <!-- Confirmation Dialog -->
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

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Floors</h1>
                <Button @click="openAddDialog" v-if="canCreate">
                    Add Floor
                </Button>
            </div>

            <Card class="w-full">
                <CardContent class="p-0">
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
                            <TableRow v-for="floor in floors" :key="floor.id">
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
                            <TableRow v-if="floors.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    No floors found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { Eye } from 'lucide-vue-next';
import { ref } from 'vue';
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

const { props } = usePage();



const floors = ref(props.floors);

const handleFloorCreated = (newFloor) => {
  // Check if the floor already exists to avoid duplicates
  if (!floors.value.some(f => f.id === newFloor.id)) {
    floors.value.unshift(newFloor);
  }
};



const managers = ref(props.managers);
const is_manager_view = ref(props.is_manager_view);
const canCreate = ref(props.canCreate);

const addFloorDialog = ref(null);
const viewDialog = ref(null);
const confirmationDialog = ref(null);
const selectedFloor = ref({});
const floorToDelete = ref(null);



const handleSuccess = () => {
  // Optional: Add any success handling logic
};

const openAddDialog = () => {
    addFloorDialog.value.open();
};

const viewFloor = (floor) => {
    selectedFloor.value = floor;
    viewDialog.value.open();
};

const openEditDialog = (floor) => {
    if (floor.can_edit) {
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
            preserveScroll: true,
            onSuccess: () => {
                floors.value = floors.value.filter(f => f.id !== floorToDelete.value.id);
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
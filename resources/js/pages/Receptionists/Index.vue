<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">
            <!-- Add Dialog -->
            <AddReceptionistDialog
                ref="addReceptionistDialog"
                :managers="managers"
                :is-manager-view="isManagerView"
                @receptionist-added="handleReceptionistAdded"
                @receptionist-removed="handleReceptionistRemoved"
            />

            <!-- View Dialog -->
            <ViewReceptionistDialog ref="viewDialog" :receptionist="selectedReceptionist" />

            <EditReceptionistDialog
                ref="editDialog"
                :managers="managers"
                :is-manager-view="isManagerView"
                @receptionist-updated="handleReceptionistUpdated"
            />

            <!-- Confirmation Dialog -->
            <ConfirmationDialog
                ref="confirmationDialog"
                title="Delete Receptionist"
                message="Are you sure you want to delete this receptionist? This action cannot be undone."
                :receptionistName="receptionistToDelete?.name || ''"
                :receptionistEmail="receptionistToDelete?.email || ''"
                :receptionistManagerName="receptionistToDelete?.manager_name || ''"
                @confirm="confirmDelete"
                @cancel="cancelDelete"
            />

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Receptionists</h1>
                <Button
                    v-if="!isManagerView || canAddReceptionist"
                    @click="openAddDialog"
                >
                    Add Receptionist
                </Button>
            </div>

            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Avatar</TableHead>
                                <TableHead>Created At</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead v-if="!isManagerView">Manager</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="receptionist in receptionists.data" :key="receptionist.id">
                                <TableCell>{{ receptionist.name }}</TableCell>
                                <TableCell>{{ receptionist.email }}</TableCell>
                                <TableCell>
                                    <img v-if="receptionist.avatar_image" :src="receptionist.avatar_image" alt="Avatar"
                                        class="h-10 w-10 rounded-full object-cover" />
                                    <div v-else
                                        class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <UserIcon class="h-6 w-6 text-gray-500" />
                                    </div>
                                </TableCell>
                                <TableCell>{{ formatDate(receptionist.created_at) }}</TableCell>
                                <TableCell>
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        receptionist.is_banned ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                    ]">
                                        {{ receptionist.is_banned ? 'Banned' : 'Active' }}
                                    </span>
                                </TableCell>
                                <TableCell v-if="!isManagerView">
                                    {{ receptionist.manager_name || 'N/A' }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="viewReceptionist(receptionist)">
                                            <Eye class="h-4 w-4 mr-1" />
                                            View
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="openEditDialog(receptionist)"
                                            :disabled="!canEditReceptionist(receptionist)"
                                        >
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>

                                        <Button
                                            :variant="receptionist.is_banned ? 'default' : 'destructive'"
                                            size="sm"
                                            @click="toggleBanStatus(receptionist)"
                                            :disabled="!canBanReceptionist(receptionist)"
                                        >
                                            <Ban class="h-4 w-4 mr-1" />
                                            {{ receptionist.is_banned ? 'Unban' : 'Ban' }}
                                        </Button>

                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="openDeleteDialog(receptionist)"
                                            :disabled="!canDeleteReceptionist(receptionist)"
                                        >
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="receptionists.data.length === 0">
                                <TableCell :colspan="isManagerView ? 6 : 7" class="text-center py-8 text-muted-foreground">
                                    No receptionists found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                     <!-- Pagination Controls -->
                     <div class="flex items-center justify-between px-4 py-3 border-t">
                        <!-- Mobile Pagination -->
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="pagination.pageIndex === 0"
                                @click="goToPage(pagination.pageIndex)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="pagination.pageIndex >= receptionists.meta.pageCount - 1"
                                @click="goToPage(pagination.pageIndex + 2)"
                            >
                                Next
                            </Button>
                        </div>

                        <!-- Desktop Pagination -->
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <!-- Results Info -->
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ receptionists.meta.from }}</span>
                                    to
                                    <span class="font-medium">{{ receptionists.meta.to }}</span>
                                    of
                                    <span class="font-medium">{{ receptionists.meta.total }}</span>
                                    results
                                </p>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex items-center space-x-2">
                                <!-- Page Size Selector -->
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-700">Rows per page:</span>
                                    <Select
                                        v-model="pagination.pageSize"
                                        @update:modelValue="handlePaginationChange"
                                    >
                                        <SelectTrigger class="w-[70px]">
                                            <SelectValue :placeholder="pagination.pageSize" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="size in [5, 10, 20, 50]" :key="size" :value="size">
                                                {{ size }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Page Navigation -->
                                <div class="flex space-x-1">
                                    <!-- First Page -->
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        :disabled="pagination.pageIndex === 0"
                                        @click="goToPage(1)"
                                    >
                                        <ChevronsLeft class="h-4 w-4" />
                                    </Button>

                                    <!-- Previous Page -->
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        :disabled="pagination.pageIndex === 0"
                                        @click="goToPage(pagination.pageIndex)"
                                    >
                                        <ChevronLeft class="h-4 w-4" />
                                    </Button>

                                    <!-- Page Numbers -->
                                    <div v-for="pageNumber in getPageNumbers()" :key="pageNumber" class="hidden md:block">
                                        <Button
                                            v-if="pageNumber !== '...'"
                                            :variant="pageNumber === pagination.pageIndex + 1 ? 'default' : 'outline'"
                                            size="sm"
                                            @click="goToPage(pageNumber)"
                                        >
                                            {{ pageNumber }}
                                        </Button>
                                        <span v-else class="px-2 py-1">...</span>
                                    </div>

                                    <!-- Next Page -->
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        :disabled="pagination.pageIndex >= receptionists.meta.pageCount - 1"
                                        @click="goToPage(pagination.pageIndex + 2)"
                                    >
                                        <ChevronRight class="h-4 w-4" />
                                    </Button>

                                    <!-- Last Page -->
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        :disabled="pagination.pageIndex >= receptionists.meta.pageCount - 1"
                                        @click="goToPage(receptionists.meta.pageCount)"
                                    >
                                        <ChevronsRight class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>


<script setup>
import { Eye, Ban, Pencil, Trash, UserIcon, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { ref, reactive, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import ConfirmationDialog from '@/components/receptionists_dialogs/ConfirmationDialog.vue';
import ViewReceptionistDialog from '@/components/receptionists_dialogs/ViewReceptionistDialog.vue';
import AddReceptionistDialog from '@/components/receptionists_dialogs/AddReceptionistDialog.vue';
import EditReceptionistDialog from '@/components/receptionists_dialogs/EditReceptionistDialog.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';

// Dialog refs
const addReceptionistDialog = ref(null);
const viewDialog = ref(null);
const editDialog = ref(null);
const confirmationDialog = ref(null);
const selectedReceptionist = ref({});
const receptionistToDelete = ref(null);

// Props and page data
const props = defineProps({
    receptionists: {
        type: Object,
        required: true
    },
    managers: {
        type: Array,
        required: true
    },
    isManagerView: {
        type: Boolean,
        default: false
    },
    currentManagerId: {
        type: Number,
        default: null
    }
});

// Page setup
const page = usePage();
const receptionists = ref(props.receptionists);
const managers = ref(props.managers);

// Pagination state
const pagination = reactive({
    pageIndex: receptionists.value.meta.pageIndex,
    pageSize: receptionists.value.meta.pageSize
});

// Computed properties for permissions
const isManager = computed(() => page.props.auth?.user?.role === 'manager');
const canAddReceptionist = computed(() => !props.isManagerView || isManager.value);

// Permission check functions
const isOwnReceptionist = (receptionist) => {
    return receptionist.manager_id === props.currentManagerId;
};

const canEditReceptionist = (receptionist) => {
    return !isManager.value || isOwnReceptionist(receptionist);
};

const canBanReceptionist = (receptionist) => {
    return !isManager.value || isOwnReceptionist(receptionist);
};

const canDeleteReceptionist = (receptionist) => {
    return !isManager.value || isOwnReceptionist(receptionist);
};

// Dialog handlers
const openAddDialog = () => {
    addReceptionistDialog.value.open();
};

const viewReceptionist = (receptionist) => {
    selectedReceptionist.value = receptionist;
    viewDialog.value.open();
};

const openEditDialog = (receptionist) => {
    editDialog.value.open({
        ...receptionist,
        user: {
            name: receptionist.name,
            email: receptionist.email,
            national_id: receptionist.national_id,
            avatar_image: receptionist.avatar_image
        },
        manager_id: receptionist.manager_id,
        is_banned: Boolean(receptionist.is_banned)
    });
};

const openDeleteDialog = (receptionist) => {
    receptionistToDelete.value = receptionist;
    confirmationDialog.value.open();
};

// CRUD operations
const handleReceptionistAdded = (receptionist) => {
    if (receptionist.is_temp) {
        const manager = props.managers.find(m => m.id === receptionist.manager_id) ||
                       { name: 'You', email: page.props.auth.user.email };

        receptionists.value.data.unshift({
            ...receptionist,
            manager_name: manager.name,
            manager_email: manager.email,
            manager_id: receptionist.manager_id
        });
    }
};

const handleReceptionistRemoved = (tempId) => {
    receptionists.value.data = receptionists.value.data.filter(r => r.id !== tempId);
};

const handleReceptionistUpdated = (updatedReceptionist) => {
    receptionists.value.data = receptionists.value.data.map(r =>
        r.id === updatedReceptionist.id ? updatedReceptionist : r
    );
};

const confirmDelete = () => {
    if (!receptionistToDelete.value) return;

    const path = props.isManagerView ? 'manager.receptionists.destroy' : 'admin.receptionists.destroy';
    router.delete(route(path, receptionistToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            receptionists.value.data = receptionists.value.data.filter(
                r => r.id !== receptionistToDelete.value.id
            );
            receptionistToDelete.value = null;
        }
    });
};

const cancelDelete = () => {
    receptionistToDelete.value = null;
};

// Replace the toggleBanStatus function with this:
const toggleBanStatus = (receptionist) => {
    const path = props.isManagerView ? 'manager.receptionists' : 'admin.receptionists';
    const endpoint = receptionist.is_banned ? 'unban' : 'ban';

    router.post(route(`${path}.${endpoint}`, receptionist.id), null, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            receptionist.is_banned = !receptionist.is_banned;
        },
        onError: (errors) => {
            console.error('Error toggling ban status:', errors);
        }
    });
};



// Pagination handlers
const handlePaginationChange = () => {
    goToPage(1); // Reset to first page when changing page size
};

const goToPage = (page) => {
    if (page === '...') return;

    const path = props.isManagerView ? 'manager.receptionists.index' : 'admin.receptionists.index';
    router.get(route(path), {
        page: page,
        pageSize: pagination.pageSize
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['receptionists']
    });
};

const getPageNumbers = () => {
    const current = pagination.pageIndex + 1;
    const total = receptionists.value.meta.pageCount;
    const delta = 1;
    const pages = [];

    if (current > 1 + delta) {
        pages.push(1);
        if (current > 2 + delta) pages.push('...');
    }

    for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
        pages.push(i);
    }

    if (current < total - delta) {
        if (current < total - 1 - delta) pages.push('...');
        pages.push(total);
    }

    return pages;
};

// Utility functions
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

// Watch for props changes
watch(() => page.props.receptionists, (newReceptionists) => {
    if (newReceptionists) {
        receptionists.value = newReceptionists;
        pagination.pageIndex = newReceptionists.meta.pageIndex;
        pagination.pageSize = newReceptionists.meta.pageSize;
    }
}, { deep: true });
</script>

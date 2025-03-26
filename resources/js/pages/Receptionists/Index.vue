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
                                <TableHead>AVATAR</TableHead>

                                <TableHead>Created_at</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead v-if="!isManagerView">Manager</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="receptionist in receptionists" :key="receptionist.id">
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
                            <TableRow v-if="receptionists.length === 0">
                                <TableCell :colspan="isManagerView ? 5 : 6" class="text-center py-8 text-muted-foreground">
                                    No receptionists found
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
import { Eye, Ban, Pencil, Trash } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import ConfirmationDialog from '@/components/receptionists_dialogs/ConfirmationDialog.vue';
import ViewReceptionistDialog from '@/components/receptionists_dialogs/ViewReceptionistDialog.vue';
import AddReceptionistDialog from '@/components/receptionists_dialogs/AddReceptionistDialog.vue';
import EditReceptionistDialog from '@/components/receptionists_dialogs/EditReceptionistDialog.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';

// Safely get page properties
const page = usePage()
const url = computed(() => page.url || '')
const pageProps = computed(() => page.props || {})

// Define props
const props = defineProps({
    receptionists: Array,
    managers: Array
});

// Computed properties
const receptionists = ref(props.receptionists);
const managers = ref(props.managers);
const isManagerView = computed(() => {
  return pageProps.value.isManagerView ||
         url.value.startsWith('/manager/receptionists')
});

// Get current manager ID from page props
const currentManagerId = computed(() => pageProps.value.currentManagerId);

// Check if current user is a manager
const isManager = computed(() => {
  return pageProps.value.auth?.user?.role === 'manager';
});

// Check if receptionist belongs to current manager
const isOwnReceptionist = (receptionist) => {
  return receptionist.manager_id === currentManagerId.value;
};

// Permission checks
const canAddReceptionist = computed(() => {
  return !isManagerView.value || isManager.value;
});

const canEditReceptionist = (receptionist) => {
  return !isManager.value || isOwnReceptionist(receptionist);
};

const canBanReceptionist = (receptionist) => {
  return !isManager.value || isOwnReceptionist(receptionist);
};

const canDeleteReceptionist = (receptionist) => {
  // Admin can always delete
  if (!isManager.value) return true;

  // Manager can only delete their own receptionists
  return isOwnReceptionist(receptionist);
};

// Date formatting
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Dialog refs and methods
const addReceptionistDialog = ref(null);
const viewDialog = ref(null);
const editDialog = ref(null);
const confirmationDialog = ref(null);
const selectedReceptionist = ref({});
const receptionistToDelete = ref(null);

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
}

const openDeleteDialog = (receptionist) => {
    receptionistToDelete.value = receptionist;
    confirmationDialog.value.open();
};

const confirmDelete = () => {
  if (receptionistToDelete.value) {
    // Check if it's a temporary receptionist
    if (receptionistToDelete.value.id.toString().startsWith('temp-')) {
      // Just remove from local state without API call
      receptionists.value = receptionists.value.filter(
        r => r.id !== receptionistToDelete.value.id
      );
      receptionistToDelete.value = null;
      return;
    }

    // Regular deletion for non-temporary receptionists
    router.delete(`/admin/receptionists/${receptionistToDelete.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        receptionists.value = receptionists.value.filter(
          r => r.id !== receptionistToDelete.value.id
        );
        receptionistToDelete.value = null;
      },
      onError: (error) => {
        console.error('Error deleting receptionist:', error);
      }
    });
  }
};


const cancelDelete = () => {
    receptionistToDelete.value = null;
};

const toggleBanStatus = (receptionist) => {
    const action = receptionist.is_banned ? 'unban' : 'ban';
    router.post(`/admin/receptionists/${receptionist.id}/${action}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            receptionists.value = receptionists.value.map(r => {
                if (r.id === receptionist.id) {
                    return { ...r, is_banned: !r.is_banned };
                }
                return r;
            });
        },
        onError: (error) => {
            console.error('Error toggling ban status:', error);
        }
    });
};



const handleReceptionistAdded = (receptionist) => {
  if (receptionist.is_temp) {
    // Add temporary receptionist
    receptionists.value = [...receptionists.value, receptionist];
  } else {
    // Replace temporary receptionist with real one
    receptionists.value = receptionists.value.map(r =>
      r.is_temp && r.email === receptionist.email ? receptionist : r
    );
  }
};

const handleReceptionistRemoved = (tempId) => {
  // Remove temporary receptionist on error
  receptionists.value = receptionists.value.filter(r => r.id !== tempId);
};

const handleReceptionistUpdated = (updatedReceptionist) => {
  receptionists.value = receptionists.value.map(r =>
    r.id === updatedReceptionist.id ? updatedReceptionist : r
  )
}

</script>

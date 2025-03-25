<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">



            <!-- Add Dialog -->
            <AddReceptionistDialog ref="addReceptionistDialog" :managers="managers" />

            <!-- View Dialog -->
            <ViewReceptionistDialog ref="viewDialog" :receptionist="selectedReceptionist" />

            <EditReceptionistDialog ref="editDialog" :managers="managers" />


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
            <Button @click="openAddDialog">Add Receptionist</Button>
            </div>

            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Created_at</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead v-if="url.startsWith('/admin/receptionists')">Manager</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="receptionist in receptionists" :key="receptionist.id">
                                <TableCell>{{ receptionist.name }}</TableCell>
                                <TableCell>{{ receptionist.email }}</TableCell>
                                <TableCell>{{ receptionist.created_at || 'N/A' }}</TableCell>
                                <TableCell>{{ receptionist.is_banned ? 'Banned' : 'Active' }}</TableCell>
                                <TableCell v-if="url.startsWith('/admin/receptionists')">
                                    {{ receptionist.manager_name || 'N/A' }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="viewReceptionist(receptionist)">
                                            <Eye class="h-4 w-4 mr-1" />
                                            View
                                        </Button>
                                        <Button variant="outline" size="sm" @click="openEditDialog(receptionist)">
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>
                                        <Button 
                                            :variant="receptionist.is_banned ? 'default' : 'destructive'" 
                                            size="sm" 
                                            @click="toggleBanStatus(receptionist)"
                                        >
                                            <Ban class="h-4 w-4 mr-1" />
                                            {{ receptionist.is_banned ? 'Unban' : 'Ban' }}
                                        </Button>
                                         <!-- Delete Button -->
                                         <Button variant="destructive" size="sm" @click="openDeleteDialog(receptionist)">
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="receptionists.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No receptionist found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Dialogs and other components remain the same -->
    </AppLayout>
</template>

<script setup>
import { Eye, Ban } from 'lucide-vue-next';
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3'; // Import usePage
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import ConfirmationDialog from '@/components/receptionists_dialogs/ConfirmationDialog.vue';
import ViewReceptionistDialog from '@/components/receptionists_dialogs/ViewReceptionistDialog.vue';
import AddReceptionistDialog from '@/components/receptionists_dialogs/AddReceptionistDialog.vue'
import EditReceptionistDialog from '@/components/receptionists_dialogs/EditReceptionistDialog.vue'


import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pencil, Trash, Loader2, User as UserIcon } from 'lucide-vue-next';

// Access the current URL
const { url } = usePage();

const props = defineProps({
    receptionists: Array,
    managers:Array
});


const receptionists = ref(props.receptionists);

console.log('Managers data:', props.managers)


const showReceptionistDialog = ref(false);
const isSubmitting = ref(false);
const errors = ref({});
const isEditing = ref(false);
const editingReceptionistId = ref(null);
const avatarInput = ref(null);
const avatarPreview = ref(null);
const avatarFile = ref(null);

const form = ref({
    name: '',
    email: '',
    password: '',
    national_id: '',
    role: 'receptionist',
    avatar: null,
    _method: null
});

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        national_id: '',
        role: 'receptionist',
        avatar: null,
        _method: null
    };
    errors.value = {};
    isEditing.value = false;
    editingReceptionistId.value = null;
    avatarPreview.value = null;
    avatarFile.value = null;
};

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    avatarFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeAvatar = () => {
    avatarPreview.value = null;
    avatarFile.value = null;
    form.value.avatar = null;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
};



const managers = ref(props.managers) // Make it reactive
const addReceptionistDialog = ref(null)

const openAddDialog = () => {
  addReceptionistDialog.value.open()
}



const confirmationDialog = ref(null);
const receptionistToDelete = ref(null);

const openDeleteDialog = (receptionist) => {
    receptionistToDelete.value = receptionist;
    confirmationDialog.value.open();
};

const confirmDelete = () => {
    if (receptionistToDelete.value) {
        router.delete(`/admin/receptionists/${receptionistToDelete.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Remove the deleted receptionist from the list
                receptionists.value = receptionists.value.filter(r => r.id !== receptionistToDelete.value.id);
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

const submitReceptionist = () => {
    isSubmitting.value = true;
    errors.value = {};

    // Create FormData for file upload
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && key !== 'avatar') {
            formData.append(key, form.value[key]);
        }
    });

    if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
    } else if (form.value.avatar === null && isEditing.value) {
        formData.append('remove_avatar', true);
    }

    if (isEditing.value) {
        formData.append('_method', 'PUT');
        router.post(`/admin/receptionists/${editingReceptionistId.value}`, formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                showReceptionistDialog.value = false;
                resetForm();
                if (page.props.receptionists) {
                    receptionists.value = page.props.receptionists;
                }
                else {
                    router.reload({ only: ['receptionists'] });
                }
            },
            onError: (formErrors) => {
                errors.value = formErrors;
                console.error('Form submission errors:', formErrors);
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } else {
        router.post('/admin/receptionists', formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                showReceptionistDialog.value = false;
                resetForm();
                if (page.props.receptionists) {
                    receptionists.value = page.props.receptionists;
                }
                else {
                    router.reload({ only: ['receptionists'] });
                }
            },
            onError: (formErrors) => {
                errors.value = formErrors;
                console.error('Form submission errors:', formErrors);
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    }
};



const viewDialog = ref(null);
const selectedReceptionist = ref({});

const viewReceptionist = (receptionist) => {
  selectedReceptionist.value = receptionist;
  viewDialog.value.open();
};



const editDialog = ref(null)

const openEditDialog = (receptionist) => {
  editDialog.value.open(receptionist)
}


/* Ban */

const toggleBanStatus = (receptionist) => {
    const action = receptionist.is_banned ? 'unban' : 'ban';
    router.post(`/admin/receptionists/${receptionist.id}/${action}`, {}, {
        preserveScroll: true,
        onSuccess: (page) => {
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


</script>

<style></style>
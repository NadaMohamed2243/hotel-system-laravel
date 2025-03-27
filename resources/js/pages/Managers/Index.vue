<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Managers</h1>
                <Button @click="openCreateDialog">Add Manager</Button>
            </div>

            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">ID</TableHead>
                                <TableHead>Avatar</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>National ID</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="manager in managers.data" :key="manager.id">
                                <TableCell class="font-medium">{{ manager.id }}</TableCell>
                                <TableCell>
                                    <img v-if="manager.avatar" :src="manager.avatar" alt="Avatar"
                                        class="h-10 w-10 rounded-full object-cover" />
                                    <div v-else
                                        class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <UserIcon class="h-6 w-6 text-gray-500" />
                                    </div>
                                </TableCell>
                                <TableCell>{{ manager.name }}</TableCell>
                                <TableCell>{{ manager.email }}</TableCell>
                                <TableCell>{{ manager.national_id || 'N/A' }}</TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="openEditDialog(manager)">
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deleteManager(manager)">
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="managers.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No managers found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <div class="flex items-center justify-between p-4">
                        <div class="text-sm text-gray-700">
                            Showing {{ managers.from }} to {{ managers.to }} of {{ managers.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Button :disabled="!managers.prev_page_url" @click="changePage(managers.current_page - 1)">
                                Previous
                            </Button>
                            <Button :disabled="!managers.next_page_url" @click="changePage(managers.current_page + 1)">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="showManagerDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Manager' : 'Add New Manager' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditing ? 'Update manager information.' : 'Create a new manager account.' }} Click save
                        when you're done.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitManager">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label>Avatar</Label>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div v-if="avatarPreview" class="h-20 w-20 rounded-full overflow-hidden">
                                        <img :src="avatarPreview" class="h-full w-full object-cover"
                                            alt="Avatar Preview" />
                                    </div>
                                    <div v-else-if="isEditing && form.avatar"
                                        class="h-20 w-20 rounded-full overflow-hidden">
                                        <img :src="form.avatar" class="h-full w-full object-cover"
                                            alt="Current Avatar" />
                                    </div>
                                    <div v-else
                                        class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                        <UserIcon class="h-10 w-10 text-gray-500" />
                                    </div>
                                </div>
                                <div>
                                    <input type="file" ref="avatarInput" class="hidden" accept="image/*"
                                        @change="handleAvatarChange" />
                                    <Button type="button" variant="outline" size="sm"
                                        @click="$refs.avatarInput.click()">
                                        Change Avatar
                                    </Button>
                                    <Button v-if="avatarPreview || (isEditing && form.avatar)" type="button"
                                        variant="ghost" size="sm" @click="removeAvatar">
                                        Remove
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="John Doe"
                                :class="{ 'border-red-500': errors.name }" />
                            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" placeholder="john@example.com"
                                :class="{ 'border-red-500': errors.email }" />
                            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                        </div>
                        <div class="grid gap-2" v-if="!isEditing">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password"
                                :class="{ 'border-red-500': errors.password }" />
                            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="national_id">National ID (Optional)</Label>
                            <Input id="national_id" v-model="form.national_id" placeholder="12345678901234"
                                :class="{ 'border-red-500': errors.national_id }" />
                            <p v-if="errors.national_id" class="text-red-500 text-sm mt-1">{{ errors.national_id }}</p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showManagerDialog = false">Cancel</Button>
                        <Button type="submit" :disabled="isSubmitting">
                            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                            {{ isEditing ? 'Update Manager' : 'Save Manager' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Manager</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this manager? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p><strong>Name:</strong> {{ managerToDelete?.name }}</p>
                    <p><strong>Email:</strong> {{ managerToDelete?.email }}</p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button type="button" variant="destructive" :disabled="isSubmitting" @click="confirmDelete">
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Delete Manager
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
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

const props = defineProps({
    managers: Object,
});

const managers = ref(props.managers);
const showManagerDialog = ref(false);
const showDeleteDialog = ref(false);
const isSubmitting = ref(false);
const errors = ref({});
const isEditing = ref(false);
const managerToDelete = ref(null);
const editingManagerId = ref(null);
const avatarInput = ref(null);
const avatarPreview = ref(null);
const avatarFile = ref(null);
const currentPage = ref(props.managers.current_page);

const form = ref({
    name: '',
    email: '',
    password: '',
    national_id: '',
    role: 'manager',
    avatar: null,
    _method: null
});

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        national_id: '',
        role: 'manager',
        avatar: null,
        _method: null
    };
    errors.value = {};
    isEditing.value = false;
    editingManagerId.value = null;
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
    form.value.remove_avatar = true;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
};

const openCreateDialog = () => {
    resetForm();
    showManagerDialog.value = true;
};

const openEditDialog = (manager) => {
    resetForm();
    isEditing.value = true;
    editingManagerId.value = manager.id;
    form.value = {
        name: manager.name,
        email: manager.email,
        national_id: manager.national_id || '',
        role: 'manager',
        avatar: manager.avatar || null
    };
    showManagerDialog.value = true;
};

const deleteManager = (manager) => {
    managerToDelete.value = manager;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    isSubmitting.value = true;

    router.delete(`/admin/managers/${managerToDelete.value.id}`, {
        data: {
            page: currentPage.value
        },
        preserveScroll: true,
        onSuccess: (page) => {
            showDeleteDialog.value = false;
            managerToDelete.value = null;
            if (page.props.managers) {
                managers.value.data = page.props.managers.data;
            }
            else {
                router.reload({ only: ['managers'] });
            }
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const submitManager = () => {
    isSubmitting.value = true;
    errors.value = {};

    // Create FormData for file upload
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && key !== 'avatar') {
            formData.append(key, form.value[key]);
        }
    });

    formData.append('page', currentPage.value);

    if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
    } else if (form.value.avatar === null && isEditing.value) {
        formData.append('remove_avatar', true);
    }

    if (isEditing.value) {
        formData.append('_method', 'PUT');
        router.post(`/admin/managers/${editingManagerId.value}`, formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                showManagerDialog.value = false;
                resetForm();
                if (page.props.managers) {
                    managers.value.data = page.props.managers.data;
                }
                else {
                    router.reload({ only: ['managers'] });
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
        router.post('/admin/managers', formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                showManagerDialog.value = false;
                resetForm();
                if (page.props.managers) {
                    managers.value = page.props.managers;
                }
                else {
                    router.reload({ only: ['managers'] });
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


const changePage = (page) => {
    router.get(
        route('managers.index', { page: page }),
        {},
        {
            preserveScroll: true,
            only: ['managers']
        }
    );
};
</script>

<style></style>
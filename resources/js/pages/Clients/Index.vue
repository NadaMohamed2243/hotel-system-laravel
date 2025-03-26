<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Eye, Loader2, Pencil, Trash } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    clients: Array,
    role: String,
    loginUser:Number,
});

const clients = ref(props.clients);
const showClientDialog = ref(false);
const showDeleteDialog = ref(false);
const isSubmitting = ref(false);
const isEditing = ref(false);
const clientToDelete = ref(null);
const editingClientId = ref(null);
const showShowDialog = ref(false);
const selectedClient = ref(null);
const errors = ref({});
const avatarPreview = ref(null);


const step = ref(1); // Step tracking for form
let login_id;
let route;
if (props.role === 'admin') {
    route = '/admin';
    login_id=props.loginUser;
} else {
    route = '/manager';
    login_id=props.loginUser;
}
console.log(props.clients.loginName);
const form = ref({
    name: '',
    email: '',
    password:'',
    phone: '',
    country: '',
    gender: 'male',
    status: 'approved',
    approved_by: login_id,
    avatar: null,
});
console.log(form.value);

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        phone: '',
        password:'',
        country: '',
        gender: 'male',
        status: 'approved',
        approved_by: login_id,
        avatar: null,
    };
    avatarPreview.value = null;
    isEditing.value = false;
    errors.value = {};
    editingClientId.value = null;
    step.value = 1;
};

const openCreateDialog = () => {
    resetForm();
    showClientDialog.value = true;
};
const openEditDialog = (client) => {
    isEditing.value = true;
    editingClientId.value = client.id;
    form.value = { ...client,approved_by:login_id,avatar: null }; // Reset avatar to avoid overriding
    showClientDialog.value = true;
};
const openShowDialog = (client) => {
    selectedClient.value = client;
    showShowDialog.value = true;
};
const deleteClient = (client) => {
    clientToDelete.value = client;
    showDeleteDialog.value = true;
};
const handleAvatarUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const validateStep1 = () => {
    errors.value = {};
    isSubmitting.value = true;

    router.post(`${route}/clients/validate-step1`, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 2;
        },
        onError: (formErrors) => {
            errors.value = formErrors;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
const handleSubmit = () => {
    isSubmitting.value = true;

    // If editing, submit immediately
    if (isEditing.value) {
        submitClient();
    } else {
        // If on step 1, validate first
        if (step.value === 1) {
            validateStep1();
        } else {
            submitClient();
        }
    }
};
const prevStep = () => {
    step.value = 1;
};

const submitClient = () => {
    isSubmitting.value = true;
    errors.value = {};

    let formData = new FormData();
    if(!isEditing.value){
    formData = new FormData();
    for (const key in form.value) {
        if (form.value[key] !== '' && form.value[key] !== null) {
                formData.append(key, form.value[key]);
        }
    }
}else{
    formData=form.value;
}
    const url = isEditing.value ? `${route}/clients/${editingClientId.value}` : `${route}/clients`;
    const method = isEditing.value ? 'put' : 'post';
    router[method](url, formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            showClientDialog.value = false;
            clients.value = page.props.clients || clients.value;
        },
        onError: (formErrors) => {
            errors.value = formErrors;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};


const confirmDelete = () => {
    isSubmitting.value = true;
    router.delete(`${route}/clients/${clientToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            showDeleteDialog.value = false;
            console.log('sucsess');
            clients.value = page.props.clients || clients.value;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Clients</h1>
                <Button @click="openCreateDialog">Add Client</Button>
            </div>
            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Country</TableHead>
                                <TableHead>Gender</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Approved By</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="client in clients" :key="client.id">
                                <TableCell>{{ client.custom_id }}</TableCell>
                                <TableCell>{{ client.name }}</TableCell>
                                <TableCell>{{ client.email }}</TableCell>
                                <TableCell>{{ client.phone }}</TableCell>
                                <TableCell>{{ client.country }}</TableCell>
                                <TableCell>{{ client.gender }}</TableCell>
                                <TableCell>{{ client.status }}</TableCell>
                                <TableCell>{{ client.approved_by }}</TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm"  @click="openShowDialog(client)">
                                            <Eye class="mr-1 h-4 w-4" />
                                            Show
                                        </Button>
                                        <Button variant="outline" size="sm" @click="openEditDialog(client)">
                                            <Pencil class="mr-1 h-4 w-4" />
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deleteClient(client)">
                                            <Trash class="mr-1 h-4 w-4" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="clients.length === 0">
                                <TableCell colspan="10" class="py-8 text-center text-muted-foreground"> No clients found </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Create Client Dialog -->
        <Dialog v-model:open="showClientDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Client' : 'Add New Client' }}</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="handleSubmit">
                    <div v-if="isEditing || step === 1">
                        <div class="grid gap-4 py-4">
                            <div>
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" placeholder="John Doe" />
                                <p v-if="errors.name" class="text-red-500">{{ errors.name }}</p>
                            </div>
                            <div>
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" />
                                <p v-if="errors.email" class="text-red-500">{{ errors.email }}</p>
                            </div>
                            <div class="grid gap-2" v-if="!isEditing">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password"
                                :class="{ 'border-red-500': errors.password }" />
                            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
                            </div>
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input id="phone" v-model="form.phone" placeholder="+123456789" />
                                <p v-if="errors.phone" class="text-red-500">{{ errors.phone }}</p>
                            </div>
                        </div>
                        <DialogFooter v-if="!isEditing">
                            <Button type="button" @click="validateStep1">Next</Button>
                        </DialogFooter>
                    </div>

                    <div v-if="isEditing || step === 2">
                        <div class="grid gap-4 py-4">
                            <div>
                                <div>
                                <Label for="country">Country</Label>
                                <Input id="country" v-model="form.country" placeholder="USA" />
                                <p v-if="errors.country" class="text-red-500">{{ errors.country }}</p>
                                </div>
                                <div class="mt-3" v-if="!isEditing" >
                                <Label id="gender">Gender</Label>
                                <div>
                                    <input id="male" type="radio" v-model="form.gender" value="male" checked />
                                    <label for="male" class="text-sm ms-1">Male</label>
                                    <input class="ms-2" type="radio" id="female" v-model="form.gender" value="female" />
                                    <label for="female" class="text-sm ms-1">Female</label>
                                </div>
                            </div>
                            </div>
                            <div v-if="!isEditing">
                                <Label for="avatar">Avatar</Label>
                                <Input id="avatar" type="file" accept="image/*" @change="handleAvatarUpload" />
                                <img v-if="avatarPreview" :src="avatarPreview" class="mt-2 h-16 w-16 rounded-full border" />
                                <p v-if="errors.avatar" class="text-red-500">{{ errors.avatar }}</p>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button v-if="!isEditing &&step === 2" type="button" @click="prevStep">Previous</Button>
                            <Button type="submit" @click="submitClient" :disabled="isSubmitting">
                                <Loader2 v-if="isSubmitting" class="animate-spin" /> {{ isEditing ? 'Update' : 'Create' }}
                            </Button>
                        </DialogFooter>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Client</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this Client? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p><strong>ID:</strong> {{ clientToDelete?.id }}</p>
                    <p><strong>Name:</strong> {{ clientToDelete?.name }}</p>
                    <p><strong>Email:</strong> {{ clientToDelete?.email }}</p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button type="button" variant="destructive" :disabled="isSubmitting" @click="confirmDelete">
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Delete Client
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="showShowDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Client Details</DialogTitle>
                    <DialogDescription>
                        Here are the details of the selected client.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p><strong>Client Name:</strong> {{ selectedClient?.name }}</p>
                    <p><strong>Email:</strong> {{ selectedClient?.email }}</p>
                    <p><strong>phone:</strong> {{ selectedClient?.phone }} </p>
                    <p><strong>country:</strong> {{ selectedClient?.country }} </p>
                    <p><strong>Status:</strong> {{ selectedClient?.status }}</p>
                    <p><strong>Approved by:</strong> {{ selectedClient?.approved_by }}</p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showShowDialog = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

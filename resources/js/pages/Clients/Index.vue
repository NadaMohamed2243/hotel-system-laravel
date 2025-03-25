<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pencil, Trash, Loader2, Eye } from 'lucide-vue-next';
// defineProps({
//     clients: Array,
// });
const props = defineProps({
    clients: Array,
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

const form = ref({
    name: '',
    email: '',
    phone: '',
    approved_by: '',
});

const resetForm = () => {
    form.value = { name: '', email: '', phone: '', approved_by: '' };
    isEditing.value = false;
    errors.value = {};
    editingClientId.value = null;
};
const openCreateDialog = () => {
    resetForm();
    showClientDialog.value = true;
};

const openEditDialog = (client) => {
    resetForm();
    isEditing.value = true;
    editingClientId.value = client.id;
    form.value = { ...client };
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

const confirmDelete = () => {
    isSubmitting.value = true;
    router.delete(`/clients/${clientToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            showDeleteDialog.value = false;
            clients.value = page.props.clients || clients.value;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
const submitClient = () => {
    isSubmitting.value = true;
    errors.value = {};

    const url = isEditing.value ? `/clients/${editingClientId.value}` : `/clients`;
    const method = isEditing.value ? 'put' : 'post';

    router[method](url, form.value, {
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
        }
    });
};


</script>
<template>
    <AppLayout>
    <h1>{{ clients}}</h1>
        <div class="flex flex-col gap-4 p-8">
            <div class="flex justify-between items-center mb-6">
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
                                <TableCell>{{ client.id }}</TableCell>
                                <TableCell>{{ client.name }}</TableCell>
                                <TableCell>{{ client.email }}</TableCell>
                                <TableCell>{{ client.phone }}</TableCell>
                                <TableCell>{{ client.country }}</TableCell>
                                <TableCell>{{ client.gender }}</TableCell>
                                <TableCell>{{ client.status }}</TableCell>
                                <TableCell>{{ client.approved_by }}</TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="openShowDialog(client)">
                                            <Eye class="h-4 w-4 mr-1" />
                                            Show
                                        </Button>
                                        <Button variant="outline" size="sm" @click="openEditDialog(client)">
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deleteClient(client)">
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="clients.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No clients found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Client</DialogTitle>
                </DialogHeader>
                <p>Are you sure you want to delete this client?</p>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

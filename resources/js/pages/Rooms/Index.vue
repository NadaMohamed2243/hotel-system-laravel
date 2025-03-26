<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import {Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pencil, Trash, Loader2 ,Eye} from 'lucide-vue-next';

const props = defineProps({
    rooms: Array,
    managers: Array,
    role: String,
    floors:Array,
});

const rooms = ref(props.rooms);
const showRoomDialog = ref(false);
const showDeleteDialog = ref(false);
const isSubmitting = ref(false);
const isEditing = ref(false);
const roomToDelete = ref(null);
const editingRoomId = ref(null);
const showShowDialog = ref(false);
const selectedRoom = ref(null);
const errors = ref({}); // Store validation errors

let route;
if(props.role==='admin'){
    route = '/admin';
}else{
    route= '/manager';
}

const form = ref({
    number: '',
    capacity: '',
    price: '',
    manager_id: props.role === 'admin' ? null : props.managers[0]?.id || null,
    floor_id: null,
});

const resetForm = () => {
    form.value = { number: '', capacity: '', price: '', manager_id: null,floor_id: null };
    isEditing.value = false;
    errors.value = {}; // Clear errors when resetting
    editingRoomId.value = null;
};

const openCreateDialog = () => {
    resetForm();
    showRoomDialog.value = true;
};

const openEditDialog = (room) => {
    resetForm();
    isEditing.value = true;
    editingRoomId.value = room.id;
    form.value = { ...room };
    showRoomDialog.value = true;
};
const openShowDialog = (room) => {
    selectedRoom.value = room;
    showShowDialog.value = true;
};
const deleteRoom = (room) => {
    roomToDelete.value = room;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    isSubmitting.value = true;
    router.delete(`${route}/rooms/${roomToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            showDeleteDialog.value = false;
            rooms.value = page.props.rooms || rooms.value;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const submitRoom = () => {
    isSubmitting.value = true;
    errors.value = {}; // Clear previous errors

    const url = isEditing.value ? `${route}/rooms/${editingRoomId.value}` : `${route}/rooms`;
    const method = isEditing.value ? 'put' : 'post';

    router[method](url, form.value, {
        preserveScroll: true,
        onSuccess: (page) => {
            showRoomDialog.value = false;
            rooms.value = page.props.rooms || rooms.value; // Update rooms without reload
        },
        onError: (formErrors) => {
            console.log("Validation Errors:", formErrors); // Debugging
            errors.value = formErrors; // Capture validation errors
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>
<template>
    <AppLayout>
        <!-- <h1>{{ props.rooms }}</h1> -->
        <div class="flex flex-col gap-4 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Rooms</h1>
                <Button @click="openCreateDialog">Add Room</Button>
            </div>
            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">ID</TableHead>
                                <TableHead >Floor Name</TableHead>
                                <TableHead>Number</TableHead>
                                <TableHead>Capacity</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead v-if="role==='admin'">Manager Name</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="room in rooms" :key="room.id">
                                <TableCell class="font-medium">{{ room.custom_id }}</TableCell>
                                <TableCell class="font-medium">{{ room.floor }}</TableCell>
                                <TableCell>{{ room.number }}</TableCell>
                                <TableCell>{{ room.capacity }}</TableCell>
                                <TableCell>{{ room.price }}$</TableCell>
                                <TableCell v-if="role==='admin'">{{ room.manager }}</TableCell>
                                <TableCell class="text-center"  v-if="room.canEdit">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="openShowDialog(room)">
                                            <Eye class="h-4 w-4 mr-1" />
                                            Show
                                        </Button>
                                        <Button variant="outline" size="sm" @click="openEditDialog(room)">
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="deleteRoom(room)">
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="rooms.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No rooms found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="showRoomDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Room' : 'Add New Room' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditing ? 'Update room details.' : 'Create a new room.' }} Click save when you're done.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitRoom">
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2" v-if="isEditing">
                        <Label for="number">Room Number</Label>
                        <Input id="number" :value="form.number" disabled
                        :class="['text-white',{ 'border-red-500': errors.number}]"/>
                        <p v-if="errors.number" class="text-red-500 text-sm">{{ errors.number }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="floor" class="text-white">Select Floor</Label>
                        <select id="floor" v-model="form.floor_id"
                            class="w-full bg-black text-white border border-gray-600 rounded-lg p-2 focus:ring-2 focus:ring-gray-400 focus:outline-none">
                            <option v-for="floor in props.floors" :key="floor.id" :value="floor.id">
                                {{ floor.name }}
                            </option>
                        </select>
                    </div>

                <div class="grid gap-2">
                    <Label for="capacity">Capacity</Label>
                    <Input id="capacity" type="number" v-model="form.capacity" placeholder="2"
                        :class="{ 'border-red-500': errors.capacity }"/>
                    <p v-if="errors.capacity" class="text-red-500 text-sm">{{ errors.capacity }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="price">Price</Label>
                    <Input id="price" type="number" v-model="form.price" placeholder="100"
                        :class="{ 'border-red-500': errors.price }"/>
                    <p v-if="errors.price" class="text-red-500 text-sm">{{ errors.price }}</p>
                </div>

                <div class="grid gap-2">
                    <select id="manager" v-model="form.manager_id"
                        class="w-full bg-black text-white border border-gray-600 rounded-lg p-2 focus:ring-2 focus:ring-gray-400 focus:outline-none"
                        disabled
                        hidden>
                        <option v-if="role === 'admin'" :value="null">Admin</option>
                        <option v-else :value="props.managers[0]?.id" >{{ props.managers[0]?.name }}</option>
                    </select>
                    <p v-if="errors.manager_id" class="text-red-500 text-sm">{{ errors.manager_id }}</p>
                </div>
            </div>

    <DialogFooter>
        <Button type="button" variant="outline" @click="showRoomDialog = false">Cancel</Button>
        <Button type="submit" :disabled="isSubmitting">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEditing ? 'Update Room' : 'Save Room' }}
        </Button>
    </DialogFooter>
</form>

            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Room</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this room? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p><strong>Room Number:</strong> {{ roomToDelete?.number }}</p>
                    <p><strong>Floor Name:</strong> {{ roomToDelete?.floor }}</p>
                    <p><strong>Capacity:</strong> {{ roomToDelete?.capacity }}</p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button type="button" variant="destructive" :disabled="isSubmitting" @click="confirmDelete">
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Delete Room
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="showShowDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Room Details</DialogTitle>
                    <DialogDescription>
                        Here are the details of the selected room.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <p><strong>Room Number:</strong> {{ selectedRoom?.number }}</p>
                    <p><strong>Floor Name:</strong> {{ selectedRoom?.floor }} </p>
                    <p><strong>Capacity:</strong> {{ selectedRoom?.capacity }}</p>
                    <p><strong>Price:</strong> {{ selectedRoom?.price }} $</p>
                    <p v-if="role==='admin'"><strong>Manager Name:</strong> {{ selectedRoom?.manager }}</p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showShowDialog = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

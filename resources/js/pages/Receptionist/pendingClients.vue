<template>
    <div>
        <Head title="Pending Clients" />

        <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Pending Clients</h1>
            </div>


            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>


                                <TableHead class="w-16 text-gray-300">ID</TableHead>
                                <TableHead class="text-gray-300">Name</TableHead>
                                <TableHead class="text-gray-300">Email</TableHead>
                                <TableHead class="text-gray-300">Mobile</TableHead>
                                <TableHead class="text-gray-300">Country</TableHead>
                                <TableHead class="text-gray-300">Gender</TableHead>
                                <TableHead class="text-center text-gray-300">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="client in clients" :key="client.id">
                                <TableCell class="font-medium text-gray-200">{{ client.user?.id || "N/A" }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.user?.name || "N/A" }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.user?.email || "N/A" }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.mobile || "N/A" }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.country || "N/A" }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.gender || "N/A" }}</TableCell>


                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="sm" @click="approveClient(client)"
                                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-700 mr-2">
                                        Approve
                                    </Button>
                                    <Button variant="outline" size="sm" @click="unapproveClient(client)"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700">
                                        Unapprove
                                    </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="clients?.length === 0" :key="'empty'">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    No pending clients found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
    </div>
</template>


<script setup lang="ts">
import AppLayout from '@/layouts/ReceptionistLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { computed } from "vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';



interface Client {
    id: number;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    mobile: string;
    country: string;
    gender: string;
}

import { PropType } from 'vue';

const props = defineProps({
    clients: {
        type: Array as PropType<Client[]>,
        required: true,
    },
});
// const clients = ref(props.clients);
const clients = computed(() => props.clients);


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Receptionist Dashboard',
        href: '/dashboard/receptionist',
    },
    {
        title: 'Pending Clients',
        href: '/dashboard/receptionist/pending-clients',
    }
];


const approveClient = (client: Client) => {
    router.patch(`/dashboard/receptionist/clients/${client.id}`);
};

const unapproveClient = (client: Client) => {

    console.log(client);
    router.delete(`/dashboard/receptionist/clients/delete/${client.id}`);
};
</script>


<style></style>

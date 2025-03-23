<script setup lang="ts">
import AppLayout from '@/layouts/ReceptionistLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed } from "vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

// Define props for receiving client data
const props = defineProps<{
    clients: any[];
}>();

// Format clients' data for the table
const formattedClients = computed(() =>
    props.clients.map(client => ({
        id: client.id,
        name: client.user?.name || "N/A",
        email: client.user?.email || "N/A",
        mobile: client.mobile || "N/A",
        country: client.country || "N/A",
        gender: client.gender || "N/A",
        // status: client.status || "Pending"
    }))
);

// Define breadcrumbs for navigation
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
</script>

<template>
    <div >
        <Head title="Pending Clients" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex flex-col gap-6 rounded-xl p-6 text-white shadow-md">
                <h2 class="text-2xl font-bold text-white">Pending Clients</h2>

                <div class="bg-[#121212] p-6 rounded-lg shadow-md">

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
                            <TableRow v-for="client in formattedClients" :key="client.id" class="hover:bg-gray-800">
                                <TableCell class="font-medium text-gray-200">{{ client.id }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.name }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.email }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.mobile }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.country }}</TableCell>
                                <TableCell class="text-gray-200">{{ client.gender }}</TableCell>
                                <!-- <TableCell class="text-center">
                                    <span class="px-2 py-1 text-sm font-semibold rounded"
                                          :class="client.status === 'Pending' ? 'bg-yellow-500 text-black' : 'bg-green-500 text-white'">
                                        {{ client.status }}
                                    </span>
                                </TableCell> -->
                                <TableCell class="text-center">
                                    <button @click="approveClient(client.id)"
                                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-700 mr-2">
                                        Approve
                                    </button>
                                    <button @click="unapproveClient(client.id)"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700">
                                        Unapprove
                                    </button>
                                </TableCell>
                            </TableRow>

                            <!-- Show this row if no clients exist -->
                            <TableRow v-if="formattedClients.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-gray-400">
                                    No pending clients found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    </div>
</template>

<style scoped>
h2 {
    font-family: 'Arial', sans-serif;
}
body {
    background-color: #121212; /* Dark background */
    color: #ffffff; /* White text for contrast */
}

.card, .table {
    background-color: #1e1e1e; /* Slightly lighter dark for content */
    color: #ffffff;
    border-radius: 8px;
}

.sidebar {
    background-color: #111111;
}

.table th, .table td {
    border-color: #333;
}

.badge {
    background-color: #28a745;
    color: #ffffff;
}

</style>

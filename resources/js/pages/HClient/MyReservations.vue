<template>
    <AppLayout>
        <!-- <ClientSidebar /> -->
        <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">My Reservations</h2>

            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Room Number</th>
                        <th class="border p-2">Accompanying People</th>
                        <th class="border p-2">Paid Price</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody v-if="reservations.length > 0">
                    <tr v-for="reservation in reservations" :key="reservation.id" class="text-center">
                        <td class="border p-2">{{ reservation.room_id }}</td>
                        <td class="border p-2">{{ reservation.accompany_number }}</td>
                        <td class="border p-2">${{ (reservation.paid_price / 100).toFixed(2) }}</td>
                        <td class="border p-2">
                            <button class="bg-red-500 text-white px-4 py-2 rounded"
                                @click="cancelReservation(reservation.id)">
                                Cancel
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="3" class="text-center p-4">No reservations available.</td>
                    </tr>
                </tbody>

            </table>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { toRaw } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientSidebar from '@/components/ClientSidebar.vue';

const props = defineProps({
    reservations: Array
});
const reservations = ref(props.reservations);

onMounted(() => {
    // router.get('/client/my-reservations', {
    //     onSuccess: (response) => {
    //         const rawReservations = toRaw(response.props.reservations);
    //         reservations.value = rawReservations;
    //     },
    //     onError: (error) => {
    //         console.error('Error fetching reservations:', error);
    //     }
    // });
});



// Function to cancel the reservation
const cancelReservation = (id) => {
    router.post('/client/cancel-reservation', { id }, {
        onSuccess: (response) => {
            console.log('Reservation canceled:', response);
            alert(response.message);
            fetchReservations();
        },
        onError: (error) => {
            console.error('Error canceling reservation:', error);
            alert('Error canceling reservation!');
        }
    });
};

</script>

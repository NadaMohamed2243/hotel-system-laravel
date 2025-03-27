<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">My Reservations</h2>

            <Table v-if="reservations.length > 0">
                <TableHeader>
                    <TableRow>
                        <TableHead>Room Number</TableHead>
                        <TableHead>Accompanying People</TableHead>
                        <TableHead>Paid Price</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="reservation in reservations" :key="reservation.id">
                        <TableCell>{{ reservation.room_number }}</TableCell>
                        <TableCell>{{ reservation.accompany_number }}</TableCell>
                        <TableCell>${{ (reservation.paid_price / 100) }}</TableCell>
                        <TableCell>
                            <Button variant="destructive" @click="cancelReservation(reservation.id)">
                                Cancel
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div v-else class="text-center p-4">
                No reservations available.
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    reservations: {
        type: Array,
        required: true
    }
});

const reservations = ref(props.reservations);

const cancelReservation = (reservationId) => {
    if (confirm('Are you sure you want to cancel this reservation?')) {
        router.delete(route('client.cancelReservation'), {
            data: { reservation_id: reservationId },
            onSuccess: () => {
                reservations.value = reservations.value.filter(res => res.id !== reservationId);
                alert('Reservation canceled successfully!');
            },
            onError: (error) => {
                console.error('Error canceling reservation:', error);
                alert('Failed to cancel reservation');
            }
        });
    }
};
</script>

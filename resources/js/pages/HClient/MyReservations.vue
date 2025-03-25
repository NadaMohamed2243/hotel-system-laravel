<template>
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-2xl font-semibold text-center mb-4">My Reservations</h2>

        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Room Number</th>
                    <th class="border p-2">Accompanying People</th>
                    <th class="border p-2">Paid Price</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="reservation in reservations" :key="reservation.id" class="text-center">
                    <td class="border p-2">{{ reservation.room_number }}</td>
                    <td class="border p-2">{{ reservation.accompany_number }}</td>
                    <td class="border p-2">${{ (reservation.paid_price / 100).toFixed(2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const reservations = ref([]);

onMounted(() => {
    router.get('/api/my-reservations', {}, {
        onSuccess: (response) => {
            reservations.value = response.props.reservations;
        }
    });
});
</script>

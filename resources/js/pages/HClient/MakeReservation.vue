<template>
    <AppLayout>
        <!-- <ClientSidebar /> -->
        <div class="max-w-6xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-6">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Choose a Room to Reserve</h1>

            <div v-if="rooms && rooms.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="room in rooms" :key="room.id"
                    class="border border-gray-300 p-6 rounded-lg shadow-md bg-gray-50 hover:shadow-lg transition mt-6">
                    <h2 class="text-xl font-semibold text-blue-600">Room Number: {{ room.number }}</h2>
                    <p class="text-gray-700 mt-2">Capacity: <span class="font-medium">{{ room.capacity }}</span> persons
                    </p>
                    <p class="text-gray-700">Price: <span class="font-medium">${{ room.price }}</span></p>

                    <p v-if="room.is_reserved" class="text-red-500 font-bold mt-2">Reserved</p>
                    <p v-else class="text-green-500 font-bold mt-2">✅ Available</p>

                    <button v-if="!room.is_reserved" @click="goToReservation(room.id)"
                        class="mt-6 w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded transition">
                        Reserve Now
                    </button>
                </div>
            </div>

            <div v-else class="text-center text-gray-500 text-lg font-medium mt-6">
                No available rooms at the moment
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientSidebar from '@/components/ClientSidebar.vue';

const props = defineProps({
    rooms: Array
});

// Redirect to the reservation form page
const goToReservation = (roomId) => {
    window.location.href = `/client/make-reservation/${roomId}`;
};
</script>

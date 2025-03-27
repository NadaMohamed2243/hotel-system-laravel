<script setup>
import { ref, defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';


const props = defineProps({
    room: Object,
    error_message: String,
});

const accompanyNumber = ref('');
const errorMessage = ref(props.error_message || '');

const submitReservation = () => {
    console.log('Accompany Number:', accompanyNumber.value);
    console.log('Room Capacity:', props.room.capacity);

    if (accompanyNumber.value > props.room.capacity) {
        errorMessage.value = `Maximum capacity is ${props.room.capacity} persons`;
        return;
    }

    errorMessage.value = '';

    axios.post('/client/reserve', {
        room_id: props.room.id,
        accompany_number: accompanyNumber.value
    })
        .then((response) => {
            console.log('Reservation successful');
            // Let the backend handle the redirect
            window.location.href = response.data.redirect;
        })
        .catch((error) => {
            console.error('Error during reservation:', error);
            errorMessage.value = "An error occurred during reservation.";
        });
};


</script>

<template>
    <div class="container mx-auto py-10">
        <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Reserve Room #{{ room.number }}</h1>

            <p class="text-gray-600">Capacity: <span class="font-medium">{{ room.capacity }} persons</span></p>
            <p class="text-gray-600">Price: <span class="font-medium text-green-600">${{ room.price }}</span></p>

            <div class="mt-4">
                <label class="block text-gray-700 font-semibold">Number of Accompany</label>
                <input v-model="accompanyNumber" type="number" min="1"
                    class="w-full mt-2 p-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" />

                <p v-if="errorMessage" class="text-red-500 text-sm mt-2">{{ errorMessage }}</p>
            </div>

            <button class="mt-6 w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-lg transition"
                @click="submitReservation">
                Proceed to Payment
            </button>
        </div>
    </div>
</template>

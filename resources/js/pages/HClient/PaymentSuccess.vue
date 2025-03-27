<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
                <p class="text-gray-600">Your room reservation has been confirmed.</p>
            </div>

            <div class="border-t border-gray-200 py-6">
                <dl class="divide-y divide-gray-200">
                    <div class="py-4 flex justify-between">
                        <dt class="text-gray-600">Reservation ID</dt>
                        <dd class="text-gray-900 font-medium">{{ reservation.id }}</dd>
                    </div>
                    <div class="py-4 flex justify-between">
                        <dt class="text-gray-600">Room Number</dt>
                        <dd class="text-gray-900 font-medium">{{ reservation.room_number }}</dd>
                    </div>
                    <div class="py-4 flex justify-between">
                        <dt class="text-gray-600">Amount Paid</dt>
                        <dd class="text-gray-900 font-medium">{{ formatPrice(reservation.paid_amount/100) }}</dd>
                    </div>
                    <div class="py-4 flex justify-between">
                        <dt class="text-gray-600">Booking Date</dt>
                        <dd class="text-gray-900 font-medium">{{ formatDate(reservation.booking_date) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 flex justify-center space-x-4">
                <button @click="goToReservations"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    View My Reservations
                </button>
                <button @click="goToBooking"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Book Another Room
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    reservation: {
        type: Object,
        required: true
    }
});

const goToReservations = () => {
    router.get('/client/my-reservations');
};

const goToBooking = () => {
    router.get('/client/make-reservation');
};

const formatPrice = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
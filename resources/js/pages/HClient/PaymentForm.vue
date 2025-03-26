<template>
  <div class="payment-container">
    <h1 class="text-center text-2xl font-bold mb-4">Payment Form</h1>

    <div v-if="paymentError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
      role="alert">
      <span class="block sm:inline">{{ paymentError }}</span>
    </div>

    <div v-if="!clientSecret"
      class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
      Loading payment form...
    </div>

    <div v-else class="bg-white p-6 rounded-lg shadow-md">
      <div class="mb-4">
        <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">
          Credit or debit card
        </label>
        <div ref="cardElement" id="card-element" class="w-full p-4 border rounded-md bg-gray-50 min-h-[40px]">
          <!-- Stripe Element will be inserted here -->
        </div>
        <div id="card-errors" role="alert" class="text-red-500 text-sm mt-2"></div>
      </div>

      <button @click="handlePayment" :disabled="isProcessing || !isCardComplete"
        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
        <span v-if="isProcessing">
          Processing...
        </span>
        <span v-else>
          Pay {{ formatPrice(amount) }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { loadStripe } from '@stripe/stripe-js';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  clientSecret: {
    type: String,
    required: true
  },
  roomId: {
    type: Number,
    required: true
  },
  amount: {
    type: Number,
    required: true
  }
});

const cardElement = ref(null);
const paymentError = ref('');
const isProcessing = ref(false);
const isCardComplete = ref(false);
let stripe = null;
let card = null;

const formatPrice = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

// Use environment variable for Stripe public key
const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);

const mountCardElement = async () => {
  try {
    stripe = await stripePromise;
    if (!stripe) {
      throw new Error('Failed to load Stripe');
    }

    const elements = stripe.elements();
    card = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#32325d',
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSmoothing: 'antialiased',
          '::placeholder': {
            color: '#aab7c4'
          }
        },
        invalid: {
          color: '#fa755a',
          iconColor: '#fa755a'
        }
      }
    });

    // Mount the card element
    card.mount('#card-element');

    // Handle real-time validation errors
    card.on('change', (event) => {
      const displayError = document.getElementById('card-errors');
      if (displayError) {
        if (event.error) {
          displayError.textContent = event.error.message;
          isCardComplete.value = false;
        } else {
          displayError.textContent = '';
          isCardComplete.value = event.complete;
        }
      }
    });

  } catch (error) {
    console.error('Stripe setup error:', error);
    paymentError.value = 'Failed to initialize payment form: ' + error.message;
  }
};

onMounted(() => {
  // Small delay to ensure DOM is ready
  setTimeout(mountCardElement, 100);
});

onUnmounted(() => {
  if (card) {
    card.destroy();
  }
});

const handlePayment = async () => {
  if (!stripe || !card) {
    paymentError.value = 'Payment system is not initialized';
    return;
  }

  isProcessing.value = true;
  paymentError.value = '';

  try {
    const { error, paymentIntent } = await stripe.confirmCardPayment(props.clientSecret, {
      payment_method: {
        card: card,
      }
    });

    if (error) {
      throw error;
    }

    if (paymentIntent.status === 'succeeded') {
      router.visit(`/client/payment-success/${props.roomId}`);
    }
  } catch (error) {
    console.error('Payment error:', error);
    paymentError.value = error.message || 'Payment failed. Please try again.';
  } finally {
    isProcessing.value = false;
  }
};
</script>

<style scoped>
.payment-container {
  max-width: 600px;
  margin: 2rem auto;
  padding: 1rem;
}
</style>
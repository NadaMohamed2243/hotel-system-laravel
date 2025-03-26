<template>
    <div class="payment-container">
      <h1 class="text-center text-2xl font-bold mb-4">Payment Form</h1>
  
      <p v-if="paymentError" class="text-red-500 text-center mb-4">{{ paymentError }}</p>
  
      <div v-if="clientSecret">
        <div class="bg-white p-6 rounded-lg shadow-md">
          <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">Card details</label>
          <div id="card-element" class="w-full p-4 border rounded-md bg-gray-50"></div>
  
          <button 
            @click="handlePayment"
            :disabled="isProcessing"
            class="bg-blue-500 text-white py-2 px-4 rounded mt-4 w-full"
          >
            Pay Now
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, nextTick } from 'vue';
  import { loadStripe } from '@stripe/stripe-js';
  
  const props = defineProps({
    clientSecret: String,
  });
  
  const paymentError = ref('');
  const isProcessing = ref(false);
  
  const stripePromise = loadStripe('your-public-key-here');  //
  
  onMounted(() => {
    if (!window.Stripe) {
      const script = document.createElement('script');
      script.src = "https://js.stripe.com/v3/";
      script.onload = () => {
        console.log("Stripe.js has been loaded");
        setTimeout(() => {
          nextTick(() => {
            initializeStripeElements();  
          });
        }, 100);  //to make sure the script is fully loaded
      };
      document.head.appendChild(script);
    } else {
      console.log("Stripe.js is already loaded");
      nextTick(initializeStripeElements);
    }
  });
  
  const initializeStripeElements = async () => {
    console.log('Initializing Stripe...');
  
    const stripe = await stripePromise;
    if (!stripe) {
      console.log('Stripe failed to load');
      paymentError.value = 'Stripe failed to load.';
      return;
    }
  
    const elements = stripe.elements();
    const card = elements.create('card');
    console.log('Stripe Elements created');
  
    nextTick(() => {
      const cardElement = document.getElementById('card-element');
      if (cardElement) {
        card.mount(cardElement);
        console.log('Card mounted successfully');
      } else {
        console.log('Card element not found in the DOM');
        paymentError.value = 'Card element not found in the DOM';
      }
    });
  };
  
  const handlePayment = async () => {
    if (!props.clientSecret) {
      paymentError.value = 'Client Secret is missing';
      return;
    }
  
    isProcessing.value = true;
  
    try {
      const stripe = await stripePromise;
      const elements = stripe.elements();
      const card = elements.getElement('card');
  
      const { error, paymentIntent } = await stripe.confirmCardPayment(props.clientSecret, {
        payment_method: {
          card: card,
        },
      });
  
      if (error) {
        paymentError.value = error.message;
        isProcessing.value = false;
      } else if (paymentIntent.status === 'succeeded') {
        console.log('Payment successful');
        window.location.href = '/payment-success';
      }
    } catch (error) {
      paymentError.value = 'Payment failed: ' + error.message;
      isProcessing.value = false;
    }
  };
  </script>
  
  <style scoped>
  .payment-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
  }
  </style>
  
<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 max-w-md w-full text-center">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-200">Your account is pending approval</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">
                Please wait until an administrator approves your account.
            </p>

            <div class="mt-6">
                <button 
                    @click="logout" 
                    class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg shadow-md transition duration-300"
                >
                    Logout
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const logout = () => {
    router.post(route('logout'));
};
</script>

<script setup lang="ts">
import { type PropType } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Tab {
    label: string;
    href: string;
    active?: boolean;
}

defineProps({
    title: {
        type: String,
        required: true
    },
    tabs: {
        type: Array as PropType<Tab[]>,
        required: true
    }
});
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-4 py-4 sm:px-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
        </div>
        <nav class="flex -mb-px space-x-8 px-4 sm:px-6">
            <Link
                v-for="tab in tabs"
                :key="tab.href"
                :href="tab.href"
                class="whitespace-nowrap border-b-2 px-1 py-4 font-medium mx-5"
                :class="{
                    'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300': tab.active,
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300': !tab.active
                }"
            >
                {{ tab.label }}
            </Link>
        </nav>
    </div>
</template>

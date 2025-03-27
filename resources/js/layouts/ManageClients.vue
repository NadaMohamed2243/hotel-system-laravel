<script setup lang="ts">
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Head } from '@inertiajs/vue3';
import TabsHeader from '@/components/TabsHeader.vue';
import { computed, watch } from 'vue';
import { route } from 'ziggy-js';
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()

// Fully reactive tabs
const tabs = computed(() => {
  const currentUrl = page.url

  return [
    {
      label: 'Pending Clients',
      href: route('receptionist.pendingClients'),
      active: currentUrl.includes('/pending')
    },
    {
      label: 'Approved Clients',
      href: route('receptionist.approvedClients'),
      active: currentUrl.includes('/approved')
    },
    {
      label: 'Reservations',
      href: route('receptionist.clientReservations'),
      active: currentUrl.includes('/reservations')
    }
  ]
})
</script>

<template>
  <AppLayout>

    <Head title="Client Management" />
    <div class="space-y-6 w-90 mx-5">
      <TabsHeader title="Manage Clients" :tabs="tabs" />
      <slot />
    </div>
  </AppLayout>
</template>

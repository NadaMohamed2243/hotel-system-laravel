<template>
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <slot></slot>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Floor Details</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Name:</Label>
            <div class="col-span-3">{{ floor.name }}</div>
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Number:</Label>
            <div class="col-span-3">{{ floor.number }}</div>
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Manager:</Label>
            <div class="col-span-3">
              {{ floor.manager_name }} ({{ floor.manager_email }})
            </div>
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Created At:</Label>
            <div class="col-span-3">{{ formatDate(floor.created_at) }}</div>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" @click="closeDialog">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
  } from '@/components/ui/dialog';
  import { Button } from '@/components/ui/button';
  import { Label } from '@/components/ui/label';
  
  const props = defineProps({
    floor: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        name: '',
        number: '',
        manager_name: '',
        manager_email: '',
        created_at: ''
      })
    }
  });
  
  
  const isOpen = ref(false);
  
  const open = () => {
    isOpen.value = true;
  };
  
  const closeDialog = () => {
    isOpen.value = false;
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  };
  
  defineExpose({
    open,
    close: closeDialog
  });
  </script>
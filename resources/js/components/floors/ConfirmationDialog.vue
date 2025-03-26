<template>
    <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
        <slot></slot>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ title }}</DialogTitle>
          <DialogDescription>
            {{ message }}
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div v-if="floorName" class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Name:</Label>
            <div class="col-span-3">{{ floorName }}</div>
          </div>
          <div v-if="floorNumber" class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Number:</Label>
            <div class="col-span-3">{{ floorNumber }}</div>
          </div>
          <div v-if="floorManagerName" class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right font-medium">Manager:</Label>
            <div class="col-span-3">{{ floorManagerName }}</div>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="cancel">
            Cancel
          </Button>
          <Button type="button" variant="destructive" @click="confirm" :disabled="isSubmitting">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
  } from '@/components/ui/dialog';
  import { Button } from '@/components/ui/button';
  import { Label } from '@/components/ui/label';
  import { Loader2 } from 'lucide-vue-next';
  
  const props = defineProps({
    title: {
      type: String,
      default: 'Delete Floor'
    },
    message: {
      type: String,
      default: 'Are you sure you want to delete this floor? This action cannot be undone.'
    },
    floorName: {
      type: String,
      default: ''
    },
    floorNumber: {
      type: String,
      default: ''
    },
    floorManagerName: {
      type: String,
      default: ''
    }
  });
  
  const emit = defineEmits(['confirm', 'cancel']);
  
  const isOpen = ref(false);
  const isSubmitting = ref(false);
  
  const open = () => {
    isOpen.value = true;
  };
  
  const close = () => {
    isOpen.value = false;
  };
  
  const confirm = () => {
    isSubmitting.value = true;
    emit('confirm');
    // The parent component should handle the actual deletion
    // and call close() when done
  };
  
  const cancel = () => {
    emit('cancel');
    close();
  };
  
  defineExpose({
    open,
    close
  });
  </script>
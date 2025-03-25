<template>
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <slot></slot>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ isEditing ? 'Edit Floor' : 'Add New Floor' }}</DialogTitle>
          <DialogDescription>
            {{ isEditing ? 'Modify the floor details' : 'Enter details for the new floor' }}
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="name" class="text-right">
              Name <span class="text-red-500">*</span>
            </Label>
            <Input
              id="name"
              v-model="form.name"
              class="col-span-3"
              placeholder="Floor name (e.g., Ground Floor)"
            />
            <span v-if="errors.name" class="col-span-4 text-sm text-red-500 text-right">
              {{ errors.name }}
            </span>
          </div>
  
          <div v-if="isAdmin" class="grid grid-cols-4 items-center gap-4">
            <Label for="manager" class="text-right">
              Manager
            </Label>
            <Select v-model="form.manager_id">
              <SelectTrigger class="col-span-3">
                <SelectValue placeholder="Select manager" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Managers</SelectLabel>
                  <SelectItem
                    v-for="manager in managers"
                    :key="manager.id"
                    :value="manager.id"
                  >
                    {{ manager.name }} ({{ manager.email }})
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="closeDialog">
            Cancel
          </Button>
          <Button type="submit" @click="submitForm" :disabled="isSubmitting">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEditing ? 'Update' : 'Create' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import { usePage } from '@inertiajs/vue3'; // Make sure this is imported
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
  import { Input } from '@/components/ui/input';
  import { Label } from '@/components/ui/label';
  import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
  } from '@/components/ui/select';
  import { Loader2 } from 'lucide-vue-next';
  
  const props = defineProps({
    managers: {
      type: Array,
      required: true
    }
  });
  
  const emit = defineEmits(['success']);
  
  // Initialize usePage() properly
  const page = usePage();
  const isAdmin = computed(() => page.url.startsWith('/admin'));
  
  const isOpen = ref(false);
  const form = ref({
    name: '',
    manager_id: null
  });
  
  const errors = ref({});
  const isSubmitting = ref(false);
  const isEditing = ref(false);
  const editingId = ref(null);
  
  const open = (floor = null) => {
    if (floor) {
      isEditing.value = true;
      editingId.value = floor.id;
      form.value = {
        name: floor.name,
        manager_id: floor.manager_id
      };
    } else {
      resetForm();
    }
    isOpen.value = true;
  };
  
  const closeDialog = () => {
    isOpen.value = false;
    resetForm();
  };
  
  const resetForm = () => {
    form.value = {
      name: '',
      manager_id: null
    };
    errors.value = {};
    isEditing.value = false;
    editingId.value = null;
  };
  
  const submitForm = async () => {
    isSubmitting.value = true;
    errors.value = {};
  
    try {
      if (isEditing.value) {
        await router.put(`/admin/floors/${editingId.value}`, form.value);
      } else {
        await router.post('/admin/floors', form.value);
      }
      emit('success');
      closeDialog();
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      }
    } finally {
      isSubmitting.value = false;
    }
  };
  
  defineExpose({
    open,
    close: closeDialog
  });
  </script>
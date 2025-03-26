<template>
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <slot></slot>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[425px]">
        <form @submit.prevent="submitForm">
          <DialogHeader>
            <DialogTitle>{{ isEditing ? 'Edit Floor' : 'Add New Floor' }}</DialogTitle>
            <DialogDescription>
              {{ isEditing ? 'Modify floor details' : 'Enter details for new floor' }}
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
                required
              />
            </div>
  
            <div v-if="isAdmin && !isEditing" class="grid grid-cols-4 items-center gap-4">
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
                      {{ manager.name }}
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
            <Button type="submit" :disabled="isSubmitting">
              <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
              {{ isEditing ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { usePage, router } from '@inertiajs/vue3';
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
  
  
  const page = usePage();
  const isAdmin = computed(() => page.props.auth.user.role === 'admin');
  const isManager = computed(() => page.props.auth.user.role === 'manager');
  
  const isOpen = ref(false);
  const form = ref({
    name: '',
    manager_id: null
  });
  const errors = ref({});
  const isSubmitting = ref(false);
  const isEditing = ref(false);
  const editingId = ref(null);
  
  onMounted(() => {
    if (!isAdmin.value) {
      form.value.manager_id = page.props.auth.user.id;
    }
  });
  
// In AddFloorDialog.vue, modify the emit definition
const emit = defineEmits(['success', 'created', 'updated']);

// Update the submitForm function
// Update the submitForm function in AddFloorDialog.vue
const submitForm = async () => {
  if (!form.value.name) {
    errors.value = { name: 'Floor name is required' };
    return;
  }

  isSubmitting.value = true;

  try {
    const urlPrefix = isAdmin.value ? '/admin' : '/manager';
    const url = isEditing.value
      ? `${urlPrefix}/floors/${editingId.value}`
      : `${urlPrefix}/floors`;

    const method = isEditing.value ? 'put' : 'post';

    await router[method](url, form.value, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Check if flash.floor exists in the response
        const responseFloor = page.props.flash?.floor;
        
        if (responseFloor) {
          const floorData = {
            id: responseFloor.id,
            name: responseFloor.name,
            number: responseFloor.number,
            manager_id: responseFloor.manager_id,
            manager_name: responseFloor.manager_name,
            created_at: responseFloor.created_at,
            can_edit: responseFloor.can_edit,
            can_delete: responseFloor.can_delete,
            is_own_floor: responseFloor.is_own_floor
          };

          if (isEditing.value) {
            emit('updated', floorData);
          } else {
            emit('created', floorData);
          }
        } else {
          // If flash.floor is not available, create a basic floor object
          // This is a fallback that will be replaced when the page refreshes
          const fallbackFloor = {
            id: isEditing.value ? editingId.value : Date.now(), // Temporary ID if creating
            name: form.value.name,
            number: 'Pending...', // Placeholder until refresh
            manager_id: form.value.manager_id,
            manager_name: isAdmin.value && form.value.manager_id ? 
              props.managers.find(m => m.id === form.value.manager_id)?.name : 
              page.props.auth.user.name,
            created_at: new Date().toISOString(),
            can_edit: true,
            can_delete: true,
            is_own_floor: true
          };
          
          if (isEditing.value) {
            emit('updated', fallbackFloor);
          } else {
            emit('created', fallbackFloor);
          }
          
          // Since we don't have complete data, refresh the page after a short delay
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        }
        
        emit('success');
        closeDialog();
      },
      onError: (err) => {
        errors.value = err;
      },
      onFinish: () => {
        isSubmitting.value = false;
      }
    });
  } catch (error) {
    console.error('Submission error:', error);
    isSubmitting.value = false;
  }
};



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
      if (isManager.value) {
        form.value.manager_id = page.props.auth.user.id;
      }
    }
    isOpen.value = true;
  };
  
 
const closeDialog = () => {
  document.activeElement.blur();
  isOpen.value = false;
  resetForm();
};

  
  const resetForm = () => {
    form.value = {
      name: '',
      manager_id: isManager.value ? page.props.auth.user.id : null
    };
    errors.value = {};
    isEditing.value = false;
    editingId.value = null;
  };
  
  defineExpose({
    open,
    close: closeDialog
  });
  </script>
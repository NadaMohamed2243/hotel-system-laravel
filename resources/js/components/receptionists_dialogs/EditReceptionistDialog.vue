<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Edit Receptionist</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submitForm" class="space-y-4" v-if="receptionist">
        <!-- Avatar Upload -->
        <div class="flex items-center justify-center">
          <div class="relative">
            <img v-if="form.avatarPreview || receptionist.avatar_image"
              :src="form.avatarPreview || receptionist.avatar_image" class="h-32 w-32 rounded-full object-cover" />
            <div v-else class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center">
              <UserIcon class="h-16 w-16 text-gray-400" />
            </div>
            <label for="avatar-upload"
              class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer">
              <Camera class="h-5 w-5" />
              <input id="avatar-upload" type="file" accept="image/*" class="hidden" @change="handleAvatarChange" />
            </label>
          </div>
        </div>

        <!-- Form Fields -->
        <div class="grid grid-cols-1 gap-4">
          <div>
            <Label for="name">Full Name</Label>
            <Input id="name" v-model="form.name" required />
          </div>

          <div>
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" required />
          </div>

          <div>
            <Label for="national_id">National ID</Label>
            <Input id="national_id" v-model="form.national_id" />
          </div>

          <div v-if="!isManagerView">
            <Label for="manager_id">Manager</Label>
            <select id="manager_id" v-model="form.manager_id" class="w-full rounded-md border border-gray-300 p-2"
              required>
              <option v-for="manager in managers" :key="manager.id" :value="manager.id"
                :selected="manager.id === form.manager_id">
                {{ manager.name }} ({{ manager.email }})
              </option>
            </select>
          </div>

          <div>
            <Label for="is_banned">Status</Label>
            <select id="is_banned" v-model="form.is_banned" class="w-full rounded-md border border-gray-300 p-2">
              <option :value="false" :selected="!form.is_banned">Active</option>
              <option :value="true" :selected="form.is_banned">Banned</option>
            </select>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="close">Cancel</Button>
          <Button type="submit" :disabled="form.processing">
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Loader2, UserIcon, Camera } from 'lucide-vue-next'

const props = defineProps({
  managers: {
    type: Array,
    default: () => [],
  },
  isManagerView: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['receptionistUpdated'])

const isOpen = ref(false)
const receptionist = ref(null)
const form = useForm({
  name: '',
  email: '',
  national_id: '',
  manager_id: '',
  is_banned: false,
  avatar: null,
  avatarPreview: null,
})



const open = (receptionistData) => {
  receptionist.value = receptionistData;
  form.name = receptionistData.user?.name || '';
  form.email = receptionistData.user?.email || '';
  form.national_id = receptionistData.user?.national_id || '';
  form.manager_id = receptionistData.manager_id || '';
  form.is_banned = Boolean(receptionistData.is_banned);
  form.avatarPreview = receptionistData.user?.avatar_image || null;

  isOpen.value = true;
}


const close = () => {
  isOpen.value = false
  form.reset()
  form.avatarPreview = null
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.avatar = file
    const reader = new FileReader()
    reader.onload = (e) => {
      form.avatarPreview = e.target.result
    }
    reader.readAsDataURL(file)
  }
}


const submitForm = () => {
  // Get current values from receptionist as fallback
  const currentValues = {
    name: receptionist.value.user?.name || '',
    email: receptionist.value.user?.email || '',
    national_id: receptionist.value.user?.national_id || '',
    manager_id: receptionist.value.manager_id || '',
    is_banned: receptionist.value.is_banned || false
  };

  // Prepare form data with fallbacks
  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('name', form.name || currentValues.name);
  formData.append('email', form.email || currentValues.email);
  formData.append('national_id', form.national_id || currentValues.national_id);
  formData.append('manager_id', form.manager_id || currentValues.manager_id);
  formData.append('is_banned', form.is_banned ? '1' : '0');

  if (form.avatar) {
    formData.append('avatar_image', form.avatar);
  }

  // Prepare optimistic update
  const updatedReceptionist = {
    ...receptionist.value,
    name: form.name || currentValues.name,
    email: form.email || currentValues.email,
    user: {
      ...(receptionist.value.user || {}),
      name: form.name || currentValues.name,
      email: form.email || currentValues.email,
      national_id: form.national_id || currentValues.national_id,
      avatar_image: form.avatarPreview || receptionist.value.user?.avatar_image || null
    },
    manager_id: form.manager_id || currentValues.manager_id,
    is_banned: Boolean(form.is_banned)
  };
  emit('receptionistUpdated', updatedReceptionist);

  // Submit using Inertia
  router.post(route('admin.receptionists.update', receptionist.value.id), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.updatedReceptionist) {
        emit('receptionistUpdated', page.props.updatedReceptionist);
      }
      close();
    },
    onError: (errors) => {
      console.error('Update failed:', errors);
      emit('receptionistUpdated', receptionist.value);
    }
  });


}


defineExpose({ open, close })
</script>

<template>
    <Dialog v-model:open="isOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Edit Receptionist</DialogTitle>
        </DialogHeader>
  
        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Avatar -->
          <div class="flex items-center justify-center">
            <div class="relative">
              <img
                v-if="form.avatarPreview"
                :src="form.avatarPreview"
                class="h-32 w-32 rounded-full object-cover"
              />
              <div
                v-else
                class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center"
              >
                <UserIcon class="h-16 w-16 text-gray-400" />
              </div>
              <label
                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer"
              >
                <Camera class="h-5 w-5" />
                <input
                  type="file"
                  class="hidden"
                  accept="image/*"
                  @change="handleAvatarChange"
                />
              </label>
            </div>
          </div>
  
          <!-- Form Fields -->
          <div class="grid gap-4">
            <div>
              <Label for="name">Full Name</Label>
              <Input
                id="name"
                v-model="form.name"
                required
              />
            </div>
  
            <div>
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                required
              />
            </div>
  
            <div>
              <Label for="national_id">National ID</Label>
              <Input
                id="national_id"
                v-model="form.national_id"
              />
            </div>
  
            <!-- Manager Selection -->
            <div>
              <Label for="manager_id">Manager</Label>
              <select
                id="manager_id"
                v-model="form.manager_id"
                class="w-full p-2 border rounded"
                required
              >
                <option v-for="manager in managers" :value="manager.id">
                  {{ manager.name }} ({{ manager.email }})
                </option>
              </select>
            </div>
  
            <!-- Status Toggle -->
            <div class="flex items-center space-x-2">
  <input
    id="is_banned"
    v-model="form.is_banned"
    type="checkbox"
    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
  />
  <Label for="is_banned">Status ({{ form.is_banned ? 'Banned' : 'Active' }})</Label>
</div>
          </div>
  
          <DialogFooter>
            <Button variant="outline" @click="close">Cancel</Button>
            <Button type="submit" :disabled="form.processing">
              <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              Update Receptionist
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useForm } from '@inertiajs/vue3'
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
      required: true
    }
  })
  
  const isOpen = ref(false)
  const receptionistToEdit = ref(null)
  
  const form = useForm({
    name: '',
    email: '',
    national_id: '',
    manager_id: '',
    is_banned: false,
    avatar: null,
    avatarPreview: null,
    _method: 'PUT'
  })
  
  const open = (receptionist) => {
    receptionistToEdit.value = receptionist
    form.name = receptionist.user.name
    form.email = receptionist.user.email
    form.national_id = receptionist.user.national_id || ''
    form.manager_id = receptionist.manager_id
    form.is_banned = receptionist.is_banned
    form.avatarPreview = receptionist.user.avatar_image 
      ? `/storage/${receptionist.user.avatar_image}` 
      : null
    isOpen.value = true
  }
  
  const handleAvatarChange = (e) => {
    const file = e.target.files[0]
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
    form.post(route('admin.receptionists.update', receptionistToEdit.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        close()
      }
    })
  }
  
  const close = () => {
    isOpen.value = false
    form.reset()
    form.avatarPreview = null
  }
  
  defineExpose({ open })
  </script>
<template>
    <Dialog v-model:open="isOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Add New Receptionist</DialogTitle>
          <DialogDescription>
            Fill in the details to add a new receptionist to the system.
          </DialogDescription>
        </DialogHeader>
  
        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Avatar Upload -->
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
                for="avatar-upload"
                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer"
              >
                <Camera class="h-5 w-5" />
                <input
                  id="avatar-upload"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleAvatarChange"
                />
              </label>
            </div>
          </div>
  
        <!-- Manager Dropdown -->
        <div>
          <Label for="manager_id">Manager</Label>
          <select
            id="manager_id"
            v-model="form.manager_id"
            class="w-full rounded-md border border-gray-300 p-2"
            required
          >
            <option value="" disabled>Select a manager</option>
            <option 
              v-for="manager in managers" 
              :key="manager.id" 
              :value="manager.id"
            >
              {{ manager.name }} ({{ manager.email }})
            </option>
          </select>
          <p v-if="errors.manager_id" class="text-sm text-red-500 mt-1">
            {{ errors.manager_id }}
          </p>
        </div>


          <!-- Form Fields -->
          <div class="grid grid-cols-1 gap-4">
            <div>
              <Label for="name">Full Name</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="John Doe"
                required
              />
              <p v-if="errors.name" class="text-sm text-red-500 mt-1">
                {{ errors.name }}
              </p>
            </div>
  
            <div>
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="john@example.com"
                required
              />
              <p v-if="errors.email" class="text-sm text-red-500 mt-1">
                {{ errors.email }}
              </p>
            </div>
  
            <div>
              <Label for="national_id">National ID</Label>
              <Input
                id="national_id"
                v-model="form.national_id"
                placeholder="1234567890"
              />
            </div>
  
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label for="password">Password</Label>
                <Input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                />
                <p v-if="errors.password" class="text-sm text-red-500 mt-1">
                  {{ errors.password }}
                </p>
              </div>
  
              <div>
                <Label for="password_confirmation">Confirm Password</Label>
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  required
                />
              </div>
            </div>
          </div>
  
          <DialogFooter>
            <Button type="button" variant="outline" @click="close">Cancel</Button>
            <Button 
                type="submit" 
                :disabled="form.processing || isSubmitting"
            >
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                {{ form.processing ? 'Adding...' : 'Add Receptionist' }}
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
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
  } from '@/components/ui/dialog'
  import { Button } from '@/components/ui/button'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Loader2, UserIcon, Camera } from 'lucide-vue-next'
  
  const props = defineProps({
    managers: {
      type: Array,
      default: () => [],
      required: true
    },
  })
  
  const isOpen = ref(false)
  const isSubmitting = ref(false)
  const errors = ref({})
  
  const form = useForm({
    name: '',
    email: '',
    national_id: '',
    password: '',
    password_confirmation: '',
    avatar: null,
    avatarPreview: null,
    manager_id: ''
  })
  
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
    if (isSubmitting.value) return // Prevent double submission
    errors.value = {}
  
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('national_id', form.national_id)
    formData.append('password', form.password)
    formData.append('password_confirmation', form.password_confirmation)
    if (form.avatar) {
      formData.append('avatar', form.avatar)
    }
  
    form.post(route('admin.receptionists.store'), {
      preserveScroll: true,
      onSuccess: () => {
        close()
        form.reset()
      },
      onError: (err) => {
        errors.value = err
      },
      onFinish: () => {
        isSubmitting.value = false
      },
    })
  }
  
  const open = () => {
    isOpen.value = true
  }
  
  const close = () => {
    isOpen.value = false
    form.reset()
    form.avatarPreview = null
    errors.value = {}
  }
  
  defineExpose({ open, close })
  </script>
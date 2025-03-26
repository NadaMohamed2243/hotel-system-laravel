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

          <!-- Manager Dropdown - Only show for admin -->
          <div v-if="showManagerDropdown">
            <Label for="manager_id">Manager</Label>
            <select
              id="manager_id"
              v-model="form.manager_id"
              class="w-full rounded-md border border-gray-300 p-2"
              :required="showManagerDropdown"
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
              <Label for="national_id">National ID</Label>
              <Input
                id="national_id"
                v-model="form.national_id"
                placeholder="1234567890"
                autocomplete="off"
              />
              <p v-if="errors.national_id" class="text-sm text-red-500 mt-1">
                {{ errors.national_id }}
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
  import { ref, computed } from 'vue'
  import { useForm } from '@inertiajs/vue3'
  import { usePage } from '@inertiajs/vue3'
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

  const page = usePage()
  const props = defineProps({
    managers: {
      type: Array,
      default: () => [],
      required: true
    },
    isManagerView: Boolean
  })

  const isOpen = ref(false)
  const isSubmitting = ref(false)
  const errors = ref({})

  // Compute if we should show manager dropdown
  const showManagerDropdown = computed(() => {
    return !props.isManagerView && props.managers.length > 0
  })

  // Initialize form with manager_id set for managers
  const form = useForm({
    name: '',
    email: '',
    national_id: '',
    password: '',
    password_confirmation: '',
    avatar: null,
    avatarPreview: null,
    manager_id: props.isManagerView ? page.props.auth.user.id : ''
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

  const emit = defineEmits(['receptionistAdded', 'receptionistRemoved'])

  const submitForm = () => {
    if (isSubmitting.value) return
    isSubmitting.value = true
    errors.value = {}

    // For managers, ensure manager_id is set to their own ID
    if (props.isManagerView) {
      form.manager_id = page.props.auth.user.id
    }

    // Create FormData
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('password', form.password)
    formData.append('password_confirmation', form.password_confirmation)
    formData.append('manager_id', form.manager_id)

    if (form.national_id) {
      formData.append('national_id', form.national_id)
    }
    if (form.avatar) {
      formData.append('avatar_image', form.avatar)
    }

    // Optimistically create temporary receptionist
    const tempReceptionist = {
      id: 'temp-' + Date.now(),
      name: form.name,
      email: form.email,
      national_id: form.national_id,
      is_banned: false,
      created_at: new Date().toISOString(),
      avatar_image: form.avatarPreview,
      manager_id: form.manager_id,
      manager_name: props.managers.find(m => m.id === form.manager_id)?.name || 'N/A',
      is_temp: true
    }

    emit('receptionistAdded', tempReceptionist)

    form.post(route(props.isManagerView ? 'manager.receptionists.store' : 'admin.receptionists.store'), {
      preserveScroll: true,
      onSuccess: () => {
        close()
        form.reset()
        if (props.isManagerView) {
          form.manager_id = page.props.auth.user.id
        }
      },
      onError: (err) => {
        errors.value = err
        emit('receptionistRemoved', tempReceptionist.id)
      },
      onFinish: () => {
        isSubmitting.value = false
      }
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
    if (props.isManagerView) {
      form.manager_id = page.props.auth.user.id
    }
  }

  defineExpose({ open, close })
  </script>

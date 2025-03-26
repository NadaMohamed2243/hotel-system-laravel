<template>
    <Dialog v-model:open="isOpen">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Receptionist Details</DialogTitle>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <!-- Avatar -->
          <div class="flex items-center justify-center">
            <img
              v-if="receptionist.avatar_image"
              :src="receptionist.avatar_image"
              alt="Avatar"
              class="h-32 w-32 rounded-full object-cover"
            />
            <div
              v-else
              class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center"
            >
              <UserIcon class="h-16 w-16 text-gray-400" />
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Name</Label>
              <div class="mt-1">{{ receptionist.name || 'N/A' }}</div>
            </div>

            <div>
              <Label>Email</Label>
              <div class="mt-1">{{ receptionist.email || 'N/A' }}</div>
            </div>

            <div>
              <Label>National ID</Label>
              <div class="mt-1">{{ receptionist.national_id || 'N/A' }}</div>
            </div>

            <div>
              <Label>Status</Label>
              <div class="mt-1">
                <Badge :variant="receptionist.is_banned ? 'destructive' : 'default'">
                  {{ receptionist.is_banned ? 'Banned' : 'Active' }}
                </Badge>
              </div>
            </div>

            <div>
              <Label>Created At</Label>
              <div class="mt-1">{{ formatDate(receptionist.created_at) || 'N/A' }}</div>
            </div>

            <div>
              <Label>Manager</Label>
              <div class="mt-1">{{ receptionist.manager_name || 'N/A' }}</div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button @click="close">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
  import { Button } from '@/components/ui/button';
  import { Label } from '@/components/ui/label';
  import { UserIcon } from 'lucide-vue-next';
import Badge from '@/components/receptionists_dialogs/Badge.vue';

  const props = defineProps({
    receptionist: {
      type: Object,
      default: () => ({}),
    },
  });

  const isOpen = ref(false);

  const open = () => {
    isOpen.value = true;
    console.log('Receptionist object:', props.receptionist);

  };

  const close = () => {
    isOpen.value = false;
  };

  const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A';
  };

  defineExpose({ open, close });


  </script>

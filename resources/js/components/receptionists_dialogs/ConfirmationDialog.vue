<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ message }}
                </DialogDescription>
            </DialogHeader>

            <!-- Display Receptionist Details -->
            <div class="space-y-2">
                <div>
                    <span class="font-medium">Name:</span> {{ receptionistName }}
                </div>
                <div>
                    <span class="font-medium">Email:</span> {{ receptionistEmail }}
                </div>
                <div v-if="receptionistManagerName">
                    <span class="font-medium">Manager:</span> {{ receptionistManagerName }}
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="onCancel">Cancel</Button>
                <Button variant="destructive" @click="onConfirm">Confirm</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = defineProps({
    title: {
        type: String,
        default: 'Are you sure?',
    },
    message: {
        type: String,
        default: 'This action cannot be undone.',
    },
    receptionistName: {
        type: String,
        default: '',
    },
    receptionistEmail: {
        type: String,
        default: '',
    },
    receptionistManagerName: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['confirm', 'cancel']);

const isOpen = ref(false);

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

const onConfirm = () => {
    emit('confirm');
    close();
};

const onCancel = () => {
    emit('cancel');
    close();
};

defineExpose({ open, close });
</script>


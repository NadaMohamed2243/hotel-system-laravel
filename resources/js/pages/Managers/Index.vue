<template>
    <AppLayout>
        <div class="flex flex-col gap-4 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Managers</h1>
                <Button>Add Manager</Button>
            </div>

            <Card class="w-full">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>National ID</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="manager in managers" :key="manager.id">
                                <TableCell class="font-medium">{{ manager.id }}</TableCell>
                                <TableCell>{{ manager.name }}</TableCell>
                                <TableCell>{{ manager.email }}</TableCell>
                                <TableCell>{{ manager.national_id || 'N/A' }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm">
                                            <Pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm">
                                            <Trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="managers.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    No managers found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/layouts/AdminAppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import { Pencil, Trash } from 'lucide-vue-next';

const props = defineProps({
    managers: Array,
});

const managers = ref(props.managers);
console.log(managers.value);

</script>

<style></style>
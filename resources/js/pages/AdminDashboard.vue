<script setup lang="ts">
import AdminAppLayout from '@/layouts/AdminAppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { computed } from 'vue';

// Get the user permissions from the usePage hook
const page = usePage();
const user = computed(() => page.props.auth.user);
const can = computed(() => page.props.auth.can || {});

// Title based on the user role
const roleTitle = computed(() => {
    if (user.value.role === 'admin') return 'Admin Dashboard';
    if (user.value.role === 'manager') return 'Manager Dashboard';
    if (user.value.role === 'receptionist') return 'Receptionist Dashboard';
    return 'Dashboard';
});
</script>

<template>

    <Head :title="roleTitle" />

    <AdminAppLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-semibold mb-6">{{ roleTitle }}</h1>

            <Tabs default-value="overview" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="management">Management</TabsTrigger>
                    <TabsTrigger value="bookings">Bookings</TabsTrigger>
                    <!-- Only show Settings tab for admins with permission -->
                    <TabsTrigger v-if="can.manage_settings" value="settings">Settings</TabsTrigger>
                </TabsList>

                <TabsContent value="overview">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Managers can see all these cards -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Stats</CardTitle>
                                <CardDescription>Overview of key metrics</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">42 Active Bookings</div>
                            </CardContent>
                        </Card>

                        <!-- Only admins and managers with permission can see financial info -->
                        <Card v-if="can.view_financial_info">
                            <CardHeader>
                                <CardTitle>Financial Overview</CardTitle>
                                <CardDescription>Revenue and financial metrics</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">$12,500</div>
                                <p class="text-muted-foreground">Monthly revenue</p>
                            </CardContent>
                        </Card>

                        <!-- All staff can see room availability -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Room Availability</CardTitle>
                                <CardDescription>Available rooms today</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">15 Rooms</div>
                                <p class="text-muted-foreground">Available for booking</p>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="management">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Only admins can manage managers -->
                        <Card v-if="can.manage_managers">
                            <CardHeader>
                                <CardTitle>Manage Managers</CardTitle>
                                <CardDescription>Add or edit manager accounts</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p>Currently 3 active managers</p>
                            </CardContent>
                            <CardFooter>
                                <Button>Manage Managers</Button>
                            </CardFooter>
                        </Card>

                        <!-- Admins and managers can manage receptionists -->
                        <Card v-if="can.manage_receptionists">
                            <CardHeader>
                                <CardTitle>Manage Receptionists</CardTitle>
                                <CardDescription>Add or edit receptionist accounts</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p>Currently 5 active receptionists</p>
                            </CardContent>
                            <CardFooter>
                                <Button>Manage Receptionists</Button>
                            </CardFooter>
                        </Card>

                        <!-- All staff can view clients, but with different permissions -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Client Management</CardTitle>
                                <CardDescription>
                                    {{ can.view_client_details ? 'Full access to client information' :
                                        'Basic client information only' }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p>Currently 25 active clients</p>
                            </CardContent>
                            <CardFooter>
                                <Button>View Clients</Button>
                            </CardFooter>
                        </Card>

                        <!-- Only admin can view system logs -->
                        <Card v-if="can.view_system_logs">
                            <CardHeader>
                                <CardTitle>System Logs</CardTitle>
                                <CardDescription>View system activity logs</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p>Last activity: 10 minutes ago</p>
                            </CardContent>
                            <CardFooter>
                                <Button>View Logs</Button>
                            </CardFooter>
                        </Card>
                    </div>
                </TabsContent>

                <TabsContent value="bookings">
                    <Card>
                        <CardHeader>
                            <CardTitle>Manage Bookings</CardTitle>
                            <CardDescription>
                                View and manage room bookings
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p>Recent bookings will be displayed here</p>
                        </CardContent>
                        <CardFooter>
                            <Button>View All Bookings</Button>
                        </CardFooter>
                    </Card>
                </TabsContent>

                <TabsContent value="settings" v-if="can.manage_settings">
                    <Card>
                        <CardHeader>
                            <CardTitle>System Settings</CardTitle>
                            <CardDescription>
                                Configure system-wide settings
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p>Only administrators can access and modify these settings</p>
                        </CardContent>
                        <CardFooter>
                            <Button>Save Settings</Button>
                        </CardFooter>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AdminAppLayout>
</template>

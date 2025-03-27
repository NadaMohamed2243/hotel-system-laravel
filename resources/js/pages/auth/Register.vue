<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

//
const countries = ref([
    "Egypt", "United States", "United Kingdom", "Canada", "France", "Germany", "India", "China", "Japan", "Australia"
]);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    country: '',
    gender: '',
    mobile: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Name Field -->
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email Field -->
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password Field -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Country Dropdown -->
                <div class="grid gap-2">
                    <Label for="country">Country</Label>
                    <select id="country" v-model="form.country" required :tabindex="5" class="border p-2 rounded">
                        <option value="" disabled>Select your country</option>
                        <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
                    </select>
                    <InputError :message="form.errors.country" />
                </div>

                <!-- Gender Dropdown -->
                <div class="grid gap-2">
                    <Label for="gender">Gender</Label>
                    <select id="gender" v-model="form.gender" required :tabindex="6" class="border p-2 rounded">
                        <option value="" disabled>Select your gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <InputError :message="form.errors.gender" />
                </div>

                <!-- Mobile Number Field -->
                <div class="grid gap-2">
                    <Label for="mobile">Mobile Number</Label>
                    <Input
                        id="mobile"
                        type="tel"
                        required
                        :tabindex="3"
                        autocomplete="tel"
                        v-model="form.mobile"
                        placeholder="Enter your mobile number"
                    />
                    <InputError :message="form.errors.mobile" />
                </div>

                <!-- Submit Button -->
                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" tabindex="7">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

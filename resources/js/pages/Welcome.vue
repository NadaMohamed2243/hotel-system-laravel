<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <div>

        <Head title="Welcome">
            <link rel="preconnect" href="https://rsms.me/" />
            <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        </Head>
        <div
            class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
            <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
                <nav class="flex items-center justify-end gap-4">
                    <Link v-if="$page.props.auth.user"
                        :href="$page.props.auth.user.role === 'client' ? route('dashboard') : route(`${$page.props.auth.user.role}.dashboard`)"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                        Log in
                        </Link>
                        <Link :href="route('register')"
                            class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                        Register
                        </Link>
                    </template>
                </nav>
            </header>
            <div
                class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
                <main
                    class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-5xl lg:flex-row">
                    <div
                        class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20">
                        <h1 class="mb-5 text-[22px] font-bold">Welcome to our hotel system</h1>
                        <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                            Experience seamless hotel management with our modern and intuitive system. Designed to
                            enhance
                            efficiency and convenience, our platform ensures smooth operations for hotel staff and an
                            effortless
                            booking experience for guests.
                        </p>
                        <ul class="mb-1 flex flex-col lg:mb-1">
                            <h2 class="mt-4 mb-3 text-[16px]">Key Features</h2>
                            <li
                                class="relative flex text-red-500 items-center gap-4 py-2 before:absolute before:bottom-0 before:left-[0.4rem] before:top-1/2 before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                <span class="relative bg-white py-1 dark:bg-[#161615]">
                                    <span
                                        class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                    </span>
                                </span>
                                <span>
                                    Effortless Bookings & Secure Payments & Billing
                                </span>
                            </li>
                            <li
                                class="relative text-red-500 flex items-center gap-4 py-2 before:absolute before:bottom-1/2 before:left-[0.4rem] before:top-0 before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                <span class="relative bg-white py-1 dark:bg-[#161615]">
                                    <span
                                        class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                    </span>
                                </span>
                                <span>
                                    Guest & Staff Management
                                </span>
                            </li>
                            <li
                                class="relative text-red-500 flex items-center gap-4 py-2 before:absolute before:bottom-1/2 before:left-[0.4rem] before:top-0 before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                <span class="relative bg-white py-1 dark:bg-[#161615]">
                                    <span
                                        class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                    </span>
                                </span>
                                <span>
                                    Rooms & Floors Management
                                </span>
                            </li>

                        </ul>

                    </div>
                    <div
                        class="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff2f2] dark:bg-[#1D0002] lg:-ml-px lg:mb-0 lg:aspect-auto lg:w-[438px] lg:rounded-r-lg lg:rounded-t-none">
                        <img src="../../../storage/app/public/elease-high-resolution-logo.png" class="h-full">
                        <div
                            class="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:overflow-hidden lg:rounded-r-lg lg:rounded-t-none" />
                    </div>
                </main>
            </div>
            <div class="h-14.5 hidden lg:block"></div>
        </div>
    </div>
</template>

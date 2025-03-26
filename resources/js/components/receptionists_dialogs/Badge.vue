<template>
    <span :class="[
      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
      variant === 'destructive' 
        ? 'bg-red-100 text-red-800' 
        : 'bg-green-100 text-green-800'
    ]">
      <slot />
    </span>
  </template>
  
  <script setup>
  defineProps({
    variant: {
      type: String,
      default: 'default'
    }
  })
  </script>
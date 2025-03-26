<template>
    <div>
      <h1>Error</h1>
      <p>{{ message }}</p>
    </div>
  </template>
  
  <script setup>
  defineProps({
      message: {
          type: String,
          required: true
      }
  });
  </script>
  
<script setup>
defineProps({
  dark: Boolean,
})
</script>

<template>
  <span class="spinner" :class="{ 'spinner--dark': dark }"></span>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Nothing here yet' },
  message: String,
  icon: { type: String, default: '◇' },
})
</script>

<template>
  <div class="state-box">
    <div class="state-box__icon">{{ icon }}</div>
    <div class="state-box__title">{{ title }}</div>
    <div v-if="message" class="state-box__msg">{{ message }}</div>
    <div v-if="$slots.default" class="mt-4">
      <slot />
    </div>
  </div>
</template>

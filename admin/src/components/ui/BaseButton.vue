<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'ghost' }, // ghost | primary | gold | danger | danger-solid
  size: { type: String, default: 'md' }, // sm | md
  block: Boolean,
  loading: Boolean,
  disabled: Boolean,
  type: { type: String, default: 'button' },
})

const classes = computed(() => [
  'btn',
  `btn--${props.variant}`,
  props.size === 'sm' ? 'btn--sm' : '',
  props.block ? 'btn--block' : '',
])
</script>

<template>
  <button :type="type" :class="classes" :disabled="disabled || loading">
    <span v-if="loading" class="spinner" style="width: 14px; height: 14px; border-width: 2px;" />
    <slot />
  </button>
</template>

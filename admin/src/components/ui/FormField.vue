<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  required: Boolean,
  hint: String,
  error: { type: [String, Array], default: '' },
})

const message = computed(() =>
  Array.isArray(props.error) ? props.error.join(' ') : props.error,
)
</script>

<template>
  <div class="field" :class="{ 'field--req': required }">
    <label v-if="label" class="field__label">{{ label }}</label>
    <slot />
    <p v-if="message" class="field__error">{{ message }}</p>
    <p v-else-if="hint" class="field__hint">{{ hint }}</p>
  </div>
</template>

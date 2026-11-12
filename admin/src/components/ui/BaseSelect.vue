<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] }, // [{ value, label }]
  invalid: Boolean,
  id: String,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <select
    :id="id"
    :value="modelValue"
    :class="['select', { 'input--error': invalid }]"
    @change="$emit('update:modelValue', $event.target.value)"
  >
    <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
  </select>
</template>

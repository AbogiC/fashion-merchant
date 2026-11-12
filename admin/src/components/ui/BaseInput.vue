<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  placeholder: String,
  invalid: Boolean,
  id: String,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <input
    :id="id"
    :type="type"
    :value="modelValue"
    :placeholder="placeholder"
    :class="['input', { 'input--error': invalid }]"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>

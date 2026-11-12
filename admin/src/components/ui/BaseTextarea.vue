<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  placeholder: String,
  invalid: Boolean,
  id: String,
  rows: { type: [String, Number], default: 4 },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <textarea
    :id="id"
    :value="modelValue"
    :rows="rows"
    :placeholder="placeholder"
    :class="['textarea', { 'textarea--error': invalid }]"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>

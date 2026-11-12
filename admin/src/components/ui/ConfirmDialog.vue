<script setup>
import BaseButton from './BaseButton.vue'
import BaseModal from './BaseModal.vue'

defineProps({
  modelValue: { type: [Boolean, Object], default: false },
  title: { type: String, default: 'Confirm' },
  message: String,
  confirmLabel: { type: String, default: 'Delete' },
  loading: Boolean,
})
const emit = defineEmits(['update:modelValue', 'confirm'])
</script>

<template>
  <BaseModal :model-value="modelValue" :title="title" @update:model-value="emit('update:modelValue', $event)">
    <p class="text-soft">{{ message }}</p>
    <template #foot>
      <BaseButton variant="ghost" @click="emit('update:modelValue', false)">Cancel</BaseButton>
      <BaseButton variant="danger-solid" :loading="loading" @click="emit('confirm')">{{ confirmLabel }}</BaseButton>
    </template>
  </BaseModal>
</template>

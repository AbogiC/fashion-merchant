<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: String,
})
const emit = defineEmits(['update:modelValue'])

const on = computed(() => props.modelValue)
function toggle() {
  emit('update:modelValue', !props.modelValue)
}
</script>

<template>
  <span class="toggle" :class="{ 'is-on': on }" role="switch" :aria-checked="on" tabindex="0"
    @click="toggle" @keydown.enter.prevent="toggle" @keydown.space.prevent="toggle">
    <span class="toggle__track"><span class="toggle__thumb"></span></span>
    <span v-if="label" class="toggle__label">{{ label }}</span>
  </span>
</template>

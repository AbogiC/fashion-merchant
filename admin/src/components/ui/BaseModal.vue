<script setup>
import { watch, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: [Boolean, Object], default: false },
  title: String,
  wide: Boolean,
})
const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}

function onKey(e) {
  if (e.key === 'Escape') close()
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) document.addEventListener('keydown', onKey)
    else document.removeEventListener('keydown', onKey)
  },
)

onUnmounted(() => document.removeEventListener('keydown', onKey))
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
      <div class="modal" :class="{ 'modal--wide': wide }">
        <div class="modal__head">
          <h2 class="modal__title">{{ title }}</h2>
          <button class="modal__close" aria-label="Close" @click="close">&times;</button>
        </div>
        <div class="modal__body">
          <slot />
        </div>
        <div v-if="$slots.foot" class="modal__foot">
          <slot name="foot" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

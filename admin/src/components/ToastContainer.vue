<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

const icons = { success: '✓', error: '!', info: 'i' }
const titles = { success: 'Success', error: 'Error', info: 'Notice' }
</script>

<template>
  <div class="toast-stack">
    <div v-for="t in toasts" :key="t.id" :class="['toast', `toast--${t.type}`]">
      <span class="toast__icon">{{ icons[t.type] }}</span>
      <div class="toast__body">
        <div class="toast__title">{{ t.title || titles[t.type] }}</div>
        <div v-if="t.message" class="toast__msg">{{ t.message }}</div>
      </div>
      <button class="toast__close" aria-label="Dismiss" @click="dismiss(t.id)">&times;</button>
    </div>
  </div>
</template>

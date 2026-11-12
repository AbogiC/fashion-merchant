<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'

defineProps({
  subtitle: String,
})
const emit = defineEmits(['menu'])

const route = useRoute()
const title = computed(() => route.meta?.title || 'Dashboard')
</script>

<template>
  <header class="topbar">
    <div class="row" style="gap: 12px">
      <button class="btn btn--ghost btn--icon sidebar-toggle" aria-label="Menu" @click="emit('menu')">☰</button>
      <div class="topbar__title">
        <h1>{{ title }}</h1>
        <div v-if="subtitle" class="topbar__subtitle">{{ subtitle }}</div>
      </div>
    </div>
    <div class="topbar__actions">
      <slot name="actions" />
    </div>
  </header>
</template>

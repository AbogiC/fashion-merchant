<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({
  open: Boolean,
})
const emit = defineEmits(['close'])

const auth = useAuthStore()

const links = [
  { to: { name: 'dashboard' }, label: 'Dashboard', icon: '▣' },
  { to: { name: 'home' }, label: 'Home Page', icon: '⌂' },
  { to: { name: 'collections' }, label: 'Collections', icon: '▦' },
  { to: { name: 'guidelines' }, label: 'Guidelines', icon: '☰' },
  { to: { name: 'mix-match' }, label: 'Mix & Match', icon: '◈' },
  { to: { name: 'news' }, label: 'News', icon: '✎' },
]

const initials = computed(() => {
  const name = auth.user?.name || auth.user?.email || 'A'
  return name.trim().charAt(0).toUpperCase()
})
</script>

<template>
  <div>
    <div v-if="open" class="scrim" @click="emit('close')"></div>
    <aside class="sidebar" :class="{ 'is-open': open }">
      <div class="sidebar__brand">
        <div class="sidebar__brand-name">Fashion Merchant</div>
        <div class="sidebar__brand-sub">Admin Console</div>
      </div>

      <nav class="sidebar__nav">
        <div class="sidebar__group-label">Content</div>
        <RouterLink
          v-for="l in links"
          :key="l.label"
          :to="l.to"
          class="nav-item"
          active-class="is-active"
          @click="emit('close')"
        >
          <span class="nav-item__icon">{{ l.icon }}</span>
          <span>{{ l.label }}</span>
        </RouterLink>
      </nav>

      <div class="sidebar__footer">
        <div class="sidebar__avatar">{{ initials }}</div>
        <div style="min-width: 0">
          <div class="sidebar__user-name truncate">{{ auth.user?.name || 'Admin' }}</div>
          <div class="sidebar__user-email">{{ auth.user?.email }}</div>
        </div>
      </div>
    </aside>
  </div>
</template>

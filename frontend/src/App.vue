<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import SiteNavbar from './components/SiteNavbar.vue'
import SiteFooter from './components/SiteFooter.vue'

// Scroll progress (0 → 1) drives the thin gold reading bar at the top edge.
const progress = ref(0)

const onScroll = () => {
  const doc = document.documentElement
  const scrollable = doc.scrollHeight - doc.clientHeight
  progress.value = scrollable > 0 ? Math.min(doc.scrollTop / scrollable, 1) : 0
}

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', onScroll)
})
</script>

<template>
  <div class="flex min-h-screen flex-col bg-luxury-cream">
    <!-- Scroll progress indicator -->
    <div class="scroll-progress" aria-hidden="true">
      <div class="scroll-progress__bar" :style="{ transform: `scaleX(${progress})` }"></div>
    </div>
    <SiteNavbar />
    <main class="flex-1">
      <RouterView v-slot="{ Component }">
        <Transition name="page" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>
    <SiteFooter />
  </div>
</template>

<style scoped>
.scroll-progress {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  z-index: 60;
  pointer-events: none;
}
.scroll-progress__bar {
  height: 100%;
  transform-origin: left;
  background: linear-gradient(90deg, #c9a962, #e0c88a, #c9a962);
  box-shadow: 0 0 10px rgba(201, 169, 98, 0.5);
  transition: transform 0.1s linear;
}
</style>

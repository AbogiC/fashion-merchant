<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'

const isOpen = ref(false)
const scrolled = ref(false)

const navLinks = [
  { name: 'Home', path: '/' },
  { name: 'Collection', path: '/collection' },
  { name: 'Guidelines', path: '/guidelines' },
  { name: 'Mix & Match', path: '/mix-match' },
  { name: 'News', path: '/news' },
  { name: 'About Us', path: '/about' },
]

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const onScroll = () => {
  scrolled.value = window.scrollY > 16
}

watch(isOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.body.style.overflow = ''
})
</script>

<template>
  <nav
    :class="[
      'fixed top-0 left-0 right-0 z-50 border-b transition-all duration-500',
      scrolled
        ? 'bg-luxury-cream/85 backdrop-blur-xl border-luxury-black/10 shadow-[0_8px_30px_-18px_rgba(10,10,10,.35)]'
        : 'bg-luxury-cream/40 backdrop-blur-sm border-transparent',
    ]"
  >
    <div class="luxury-container">
      <div
        :class="[
          'flex items-center justify-between transition-all duration-500',
          scrolled ? 'h-16 md:h-20' : 'h-20 md:h-24',
        ]"
      >
        <RouterLink
          to="/"
          class="group flex items-center text-2xl md:text-3xl font-serif tracking-luxury text-luxury-black"
          @click="isOpen = false"
        >
          <span class="transition-all duration-500 group-hover:tracking-[0.4em]">MAISON</span>
        </RouterLink>

        <div class="hidden lg:flex items-center space-x-10">
          <RouterLink
            v-for="link in navLinks"
            :key="link.name"
            :to="link.path"
            class="link-underline text-xs tracking-luxury uppercase text-luxury-black/70 hover:text-luxury-black transition-colors duration-300"
          >
            {{ link.name }}
          </RouterLink>
        </div>

        <button
          @click="toggleMenu"
          class="lg:hidden relative flex flex-col justify-center items-center space-y-1.5 p-2"
          aria-label="Toggle menu"
          :aria-expanded="isOpen"
        >
          <span
            :class="['block w-6 h-px bg-luxury-black transition-all duration-300', isOpen ? 'rotate-45 translate-y-2' : '']"
          ></span>
          <span
            :class="['block w-6 h-px bg-luxury-black transition-all duration-300', isOpen ? 'opacity-0' : '']"
          ></span>
          <span
            :class="['block w-6 h-px bg-luxury-black transition-all duration-300', isOpen ? '-rotate-45 -translate-y-2' : '']"
          ></span>
        </button>
      </div>
    </div>

    <div
      :class="[
        'lg:hidden fixed inset-x-0 bottom-0 transition-all duration-500',
        scrolled ? 'top-16 md:top-20' : 'top-20 md:top-24',
        isOpen ? 'opacity-100 visible' : 'opacity-0 invisible pointer-events-none',
      ]"
    >
      <div
        class="absolute inset-0 bg-luxury-black/20 backdrop-blur-sm"
        @click="isOpen = false"
      ></div>
      <div
        :class="[
          'relative flex h-full flex-col items-center justify-center gap-8 bg-luxury-cream transition-transform duration-500',
          isOpen ? 'translate-y-0' : '-translate-y-4',
        ]"
      >
        <RouterLink
          v-for="(link, i) in navLinks"
          :key="link.name"
          :to="link.path"
          @click="isOpen = false"
          :class="[
            'text-2xl font-serif tracking-luxury text-luxury-black/80 hover:text-luxury-black transition-all duration-500',
            isOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3',
          ]"
          :style="{ transitionDelay: isOpen ? `${i * 60 + 120}ms` : '0ms' }"
        >
          {{ link.name }}
        </RouterLink>
      </div>
    </div>
  </nav>
</template>

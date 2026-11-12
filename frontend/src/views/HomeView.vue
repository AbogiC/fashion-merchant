<script setup>
import { onMounted, ref } from 'vue'
import { contentApi } from '@/api/content'

const home = ref({
  hero: {
    eyebrow: '',
    title_primary: '',
    title_secondary: '',
    description: '',
    cta_label: '',
    cta_url: '/collection',
  },
  featured: [],
  stats: [],
  marquee: [],
  philosophy: {
    subtitle: '',
    title_primary: '',
    title_secondary: '',
    body: [],
    cta_label: '',
    cta_url: '/about',
    monogram: 'M',
  },
})
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    home.value = await contentApi.home()
  } catch (e) {
    error.value = e.message || 'Unable to load the home page.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-luxury-black">
      <div class="hero-bg absolute inset-0"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-luxury-black/60 via-transparent to-luxury-black"></div>
      <div class="hero-orb animate-float absolute -left-1/4 top-1/4 h-[28rem] w-[28rem] rounded-full blur-3xl"></div>
      <div class="hero-orb absolute -right-1/4 bottom-1/4 h-[24rem] w-[24rem] rounded-full blur-3xl" style="animation: float 9s ease-in-out infinite;"></div>

      <div class="relative z-10 px-6 text-center">
        <template v-if="loading">
          <p class="animate-pulse text-xs uppercase tracking-luxury text-luxury-cream/50">Loading…</p>
        </template>
        <p v-else-if="error" class="mx-auto max-w-md text-sm text-red-300">{{ error }}</p>
        <template v-else>
          <p class="animate-fade-up mb-6 text-xs uppercase tracking-wide-luxury text-luxury-gold">{{ home.hero.eyebrow }}</p>
          <h1 class="animate-fade-up anim-delay-1 mb-8 text-5xl font-serif font-light tracking-wide text-luxury-cream md:text-7xl lg:text-8xl">
            {{ home.hero.title_primary }}<br />{{ home.hero.title_secondary }}
          </h1>
          <p class="animate-fade-up anim-delay-2 mx-auto mb-10 max-w-md text-sm font-light leading-relaxed text-luxury-cream/70 md:text-base">
            {{ home.hero.description }}
          </p>
          <div class="animate-fade-up anim-delay-3">
            <RouterLink :to="home.hero.cta_url || '/collection'" class="luxury-btn-gold">{{ home.hero.cta_label }}</RouterLink>
          </div>
        </template>
      </div>

      <div class="animate-fade-in anim-delay-5 absolute bottom-10 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center gap-3">
        <span class="text-[10px] uppercase tracking-luxury text-luxury-cream/40">Scroll</span>
        <div class="relative h-12 w-px overflow-hidden bg-luxury-cream/15">
          <span class="animate-scroll-down absolute left-0 top-0 h-4 w-px bg-luxury-gold"></span>
        </div>
      </div>
    </section>

    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mb-16 text-center md:mb-24">
          <p class="section-subtitle mb-4">The Edit</p>
          <h2 class="section-title">Curated Essentials</h2>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
          <RouterLink
            v-for="(item, i) in home.featured"
            :key="item.id"
            :to="item.link_url || '/collection'"
            v-reveal="i"
            class="media group relative block aspect-[4/5] overflow-hidden"
          >
            <img
              v-if="item.image_url"
              :src="item.image_url"
              :alt="item.title"
              loading="lazy"
              class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="media-inner"></div>
            <div v-if="!item.image_url" class="media-monogram">{{ item.monogram || item.title.charAt(0) }}</div>
            <span class="media-sheen"></span>
            <div class="absolute inset-0 z-10 bg-gradient-to-t from-luxury-black/80 via-luxury-black/10 to-transparent"></div>
            <div class="absolute inset-x-8 bottom-8 z-20 flex items-end justify-between">
              <div>
                <p class="mb-2 text-xs uppercase tracking-luxury text-luxury-gold">{{ item.tag }}</p>
                <h3 class="text-2xl font-serif text-luxury-cream md:text-3xl">{{ item.title }}</h3>
                <p class="mt-2 translate-y-2 text-xs uppercase tracking-wide text-luxury-cream/60 opacity-0 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100">
                  {{ item.copy }}
                </p>
              </div>
              <span class="translate-x-2 text-lg text-luxury-gold opacity-0 transition-all duration-500 group-hover:translate-x-0 group-hover:opacity-100">→</span>
            </div>
          </RouterLink>
        </div>
      </div>
    </section>

    <section class="overflow-hidden border-y border-luxury-black/10 bg-luxury-cream py-6">
      <div class="marquee">
        <div class="marquee-track">
          <span v-for="n in 2" :key="n" class="flex shrink-0 items-center">
            <span
              v-for="word in home.marquee"
              :key="word + n"
              class="mx-8 text-sm uppercase tracking-luxury text-luxury-gray/70"
            >
              {{ word }} <span class="ml-16 text-luxury-gold">✦</span>
            </span>
          </span>
        </div>
      </div>
    </section>

    <section class="bg-luxury-black py-20 md:py-28">
      <div class="luxury-container">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
          <div v-for="(stat, i) in home.stats" :key="stat.id" v-reveal="i" class="text-center">
            <p class="font-serif text-4xl text-luxury-gold md:text-5xl">
              {{ stat.value }}<span class="text-2xl">{{ stat.suffix }}</span>
            </p>
            <p class="mt-3 text-xs uppercase tracking-luxury text-luxury-cream/50">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-luxury-light py-24 md:py-32">
      <div class="luxury-container">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
          <div v-reveal>
            <p class="section-subtitle mb-6">{{ home.philosophy.subtitle }}</p>
            <h2 class="section-title mb-8">{{ home.philosophy.title_primary }}<br />{{ home.philosophy.title_secondary }}</h2>
            <div class="mb-10 space-y-6">
              <p v-for="(paragraph, i) in home.philosophy.body" :key="i" class="leading-relaxed text-luxury-gray">
                {{ paragraph }}
              </p>
            </div>
            <RouterLink :to="home.philosophy.cta_url || '/about'" class="luxury-btn">{{ home.philosophy.cta_label }}</RouterLink>
          </div>
          <div v-reveal="1" class="media group relative aspect-square">
            <div class="media-inner"></div>
            <div class="media-monogram">{{ home.philosophy.monogram }}</div>
            <span class="media-sheen"></span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

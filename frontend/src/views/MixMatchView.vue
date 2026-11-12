<script setup>
import { onMounted, ref } from 'vue'
import { contentApi } from '@/api/content'

const looks = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const res = await contentApi.mixMatch()
    looks.value = res.looks ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load the looks.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mb-16 text-center md:mb-24">
          <p class="section-subtitle mb-4">Curated Combinations</p>
          <h1 class="section-title">Mix &amp; Match</h1>
        </div>

        <p v-if="loading" class="mb-12 text-center text-xs uppercase tracking-luxury text-luxury-gray">
          Loading…
        </p>
        <p v-else-if="error" class="mb-12 text-center text-sm text-red-600">{{ error }}</p>
        <p v-else-if="looks.length === 0" class="mb-12 text-center text-sm text-luxury-gray">
          No looks published yet.
        </p>

        <div class="space-y-16 md:space-y-24">
          <div
            v-for="(look, i) in looks"
            :key="look.id"
            class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-16"
          >
            <div
              :class="['media group relative aspect-square', i % 2 === 0 ? 'order-2 lg:order-1' : 'order-2']"
              v-reveal
            >
              <img
                v-if="look.image_url"
                :src="look.image_url"
                :alt="look.title"
                loading="lazy"
                class="absolute inset-0 h-full w-full object-cover"
              />
              <div class="media-inner"></div>
              <div v-if="!look.image_url" class="media-monogram">{{ look.monogram || look.title.charAt(0) }}</div>
              <span class="media-sheen"></span>
            </div>
            <div :class="i % 2 === 0 ? 'order-1 lg:order-2' : 'order-1'" v-reveal="1">
              <p class="section-subtitle mb-4">{{ look.label }}</p>
              <h2 class="mb-6 text-3xl font-serif md:text-4xl">{{ look.title }}</h2>
              <p class="mb-4 leading-relaxed text-luxury-gray">{{ look.description }}</p>
              <p class="text-sm text-luxury-gray/70">{{ look.pieces }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

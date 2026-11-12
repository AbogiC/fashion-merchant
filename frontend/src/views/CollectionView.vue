<script setup>
import { onMounted, ref } from 'vue'
import { contentApi } from '@/api/content'

const collections = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const res = await contentApi.collections()
    collections.value = res.collections ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load the collection.'
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
          <p class="section-subtitle mb-4">Autumn / Winter 2025</p>
          <h1 class="section-title">The Collection</h1>
        </div>

        <p v-if="loading" class="mb-12 text-center text-xs uppercase tracking-luxury text-luxury-gray">
          Loading…
        </p>
        <p v-else-if="error" class="mb-12 text-center text-sm text-red-600">{{ error }}</p>
        <p v-else-if="collections.length === 0" class="mb-12 text-center text-sm text-luxury-gray">
          No collections published yet.
        </p>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
          <div
            v-for="(item, i) in collections"
            :key="item.id"
            v-reveal="i"
            class="media group relative aspect-[3/4] cursor-pointer overflow-hidden"
          >
            <img
              v-if="item.image_url"
              :src="item.image_url"
              :alt="item.name"
              loading="lazy"
              class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="media-inner"></div>
            <div v-if="!item.image_url" class="media-monogram">{{ item.monogram || item.name.charAt(0) }}</div>
            <span class="media-sheen"></span>
            <div class="absolute inset-0 z-10 bg-gradient-to-t from-luxury-black/80 via-luxury-black/10 to-transparent"></div>
            <div class="absolute inset-x-8 bottom-8 z-20 flex items-end justify-between">
              <div>
                <p class="mb-2 text-xs uppercase tracking-luxury text-luxury-gold">{{ item.season }} · {{ item.pieces }} Pieces</p>
                <h2 class="text-3xl font-serif text-luxury-cream md:text-4xl">{{ item.name }}</h2>
              </div>
              <span class="translate-x-2 text-lg text-luxury-gold opacity-0 transition-all duration-500 group-hover:translate-x-0 group-hover:opacity-100">→</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

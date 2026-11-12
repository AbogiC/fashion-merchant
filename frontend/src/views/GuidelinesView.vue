<script setup>
import { onMounted, ref } from 'vue'
import { contentApi } from '@/api/content'

const guidelines = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const res = await contentApi.guidelines()
    guidelines.value = res.guidelines ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load the guidelines.'
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
          <p class="section-subtitle mb-4">The Principles</p>
          <h1 class="section-title">Style Guidelines</h1>
        </div>

        <p v-if="loading" class="mb-12 text-center text-xs uppercase tracking-luxury text-luxury-gray">
          Loading…
        </p>
        <p v-else-if="error" class="mb-12 text-center text-sm text-red-600">{{ error }}</p>
        <p v-else-if="guidelines.length === 0" class="mb-12 text-center text-sm text-luxury-gray">
          No guidelines published yet.
        </p>

        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-16">
          <div
            v-for="(item, i) in guidelines"
            :key="item.id"
            v-reveal="i % 2"
            class="group border-t border-luxury-black/10 pt-8 transition-colors duration-500 hover:border-luxury-gold"
          >
            <p class="mb-4 text-xs tracking-luxury text-luxury-gold transition-transform duration-500 group-hover:translate-x-1.5">{{ item.number }}</p>
            <h2 class="mb-4 text-2xl font-serif md:text-3xl transition-transform duration-500 group-hover:translate-x-1.5">{{ item.title }}</h2>
            <p class="leading-relaxed text-luxury-gray">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

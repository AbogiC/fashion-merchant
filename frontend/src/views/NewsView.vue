<script setup>
import { onMounted, ref } from 'vue'
import { contentApi } from '@/api/content'

const news = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const res = await contentApi.news()
    news.value = res.news ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load the journal.'
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
          <p class="section-subtitle mb-4">Journal</p>
          <h1 class="section-title">News &amp; Stories</h1>
        </div>

        <p v-if="loading" class="mb-12 text-center text-xs uppercase tracking-luxury text-luxury-gray">
          Loading…
        </p>
        <p v-else-if="error" class="mb-12 text-center text-sm text-red-600">{{ error }}</p>
        <p v-else-if="news.length === 0" class="mb-12 text-center text-sm text-luxury-gray">
          No stories published yet.
        </p>

        <div class="mx-auto max-w-3xl space-y-12 md:space-y-16">
          <article
            v-for="(item, i) in news"
            :key="item.id"
            v-reveal="i"
            class="group cursor-pointer border-b border-luxury-black/10 pb-12 transition-colors duration-500 hover:border-luxury-gold md:pb-16"
          >
            <div class="flex items-start justify-between gap-6">
              <div>
                <p class="mb-4 text-xs uppercase tracking-luxury text-luxury-gold">{{ item.date }}</p>
                <h2 class="mb-4 text-2xl font-serif transition-colors duration-300 group-hover:text-luxury-gold md:text-3xl">
                  {{ item.title }}
                </h2>
                <p class="leading-relaxed text-luxury-gray">{{ item.excerpt }}</p>
              </div>
              <span class="mt-1 translate-x-2 text-lg text-luxury-gold opacity-0 transition-all duration-500 group-hover:translate-x-0 group-hover:opacity-100">→</span>
            </div>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>

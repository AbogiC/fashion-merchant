<script setup>
const news = [
  {
    date: 'November 2025',
    title: 'The Art of Slow Fashion',
    excerpt: 'Why we believe in quality over quantity, and how it shapes every decision we make.'
  },
  {
    date: 'October 2025',
    title: 'Behind the Seams: Our Atelier',
    excerpt: 'A glimpse into the craftsmanship and hands that bring our collections to life.'
  },
  {
    date: 'September 2025',
    title: 'AW25 Lookbook Preview',
    excerpt: 'An exclusive first look at the silhouettes and textures defining the season ahead.'
  },
]
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mb-16 text-center md:mb-24">
          <p class="section-subtitle mb-4">Journal</p>
          <h1 class="section-title">News &amp; Stories</h1>
        </div>

        <div class="mx-auto max-w-3xl space-y-12 md:space-y-16">
          <article
            v-for="(item, i) in news"
            :key="item.title"
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

<script setup>
const guidelines = [
  {
    number: '01',
    title: 'Fit First',
    description: 'A garment should move with you, not against you. We prioritize silhouette and drape over fleeting trends.'
  },
  {
    number: '02',
    title: 'Quality Over Quantity',
    description: 'Invest in fewer, better pieces. Each item in our collection is designed to be worn for decades, not seasons.'
  },
  {
    number: '03',
    title: 'Neutral Foundations',
    description: 'Build your wardrobe around a palette of blacks, creams, and earth tones. These are the colors that endure.'
  },
  {
    number: '04',
    title: 'Considered Details',
    description: 'Pay attention to the small things—the weight of a button, the finish of a hem. These are the marks of true quality.'
  },
]
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mb-16 text-center md:mb-24">
          <p class="section-subtitle mb-4">The Principles</p>
          <h1 class="section-title">Style Guidelines</h1>
        </div>

        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-16">
          <div
            v-for="(item, i) in guidelines"
            :key="item.number"
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

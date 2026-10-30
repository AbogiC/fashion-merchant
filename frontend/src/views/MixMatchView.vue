<script setup>
const looks = [
  {
    number: 'Look 01',
    title: 'The Monochrome Edit',
    description: 'Head-to-toe cream creates a column of uninterrupted elegance. The key is texture—vary the weights and weaves to add depth without breaking the silhouette.',
    pieces: 'Cashmere Turtleneck · Wool Trousers · Leather Loafers',
    letter: 'M',
  },
  {
    number: 'Look 02',
    title: 'Tailored Ease',
    description: 'Soft tailoring is the foundation of modern luxury. Pair an unstructured blazer with relaxed trousers for a look that moves effortlessly from day to evening.',
    pieces: 'Linen Blazer · Silk Shirt · Wide-Leg Trousers',
    letter: 'T',
  },
]
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mb-16 text-center md:mb-24">
          <p class="section-subtitle mb-4">Curated Combinations</p>
          <h1 class="section-title">Mix &amp; Match</h1>
        </div>

        <div class="space-y-16 md:space-y-24">
          <div
            v-for="(look, i) in looks"
            :key="look.number"
            class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-16"
          >
            <div
              :class="['media group relative aspect-square', i % 2 === 0 ? 'order-2 lg:order-1' : 'order-2']"
              v-reveal
            >
              <div class="media-inner"></div>
              <div class="media-monogram">{{ look.letter }}</div>
              <span class="media-sheen"></span>
            </div>
            <div :class="i % 2 === 0 ? 'order-1 lg:order-2' : 'order-1'" v-reveal="1">
              <p class="section-subtitle mb-4">{{ look.number }}</p>
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

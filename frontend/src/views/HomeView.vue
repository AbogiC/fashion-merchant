<script setup>
const featured = [
  { tag: 'Outerwear', title: 'The Sculpted Coat', copy: 'Structure meets softness', letter: 'O' },
  { tag: 'Knitwear', title: 'Cashmere Collection', copy: 'Warmth, refined', letter: 'K' },
]

const stats = [
  { value: '25', suffix: '+', label: 'Years of Craft' },
  { value: '100', suffix: '%', label: 'Natural Fibres' },
  { value: '12', suffix: '', label: 'Artisan Ateliers' },
  { value: '∞', suffix: '', label: 'Timeless Pieces' },
]

const marquee = [
  'Crafted with Intention',
  'Slow Fashion',
  'Natural Materials',
  'Timeless Design',
  'Hand Finished',
  'Considered Details',
]
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-luxury-black">
      <div class="hero-bg absolute inset-0"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-luxury-black/60 via-transparent to-luxury-black"></div>
      <div class="hero-orb animate-float absolute -left-1/4 top-1/4 h-[28rem] w-[28rem] rounded-full blur-3xl"></div>
      <div class="hero-orb absolute -right-1/4 bottom-1/4 h-[24rem] w-[24rem] rounded-full blur-3xl" style="animation: float 9s ease-in-out infinite;"></div>

      <div class="relative z-10 px-6 text-center">
        <p class="animate-fade-up mb-6 text-xs uppercase tracking-wide-luxury text-luxury-gold">Autumn / Winter 2025</p>
        <h1 class="animate-fade-up anim-delay-1 mb-8 text-5xl font-serif font-light tracking-wide text-luxury-cream md:text-7xl lg:text-8xl">
          Timeless<br />Elegance
        </h1>
        <p class="animate-fade-up anim-delay-2 mx-auto mb-10 max-w-md text-sm font-light leading-relaxed text-luxury-cream/70 md:text-base">
          Discover the art of refined dressing. Each piece is crafted with intention, designed for the discerning individual.
        </p>
        <div class="animate-fade-up anim-delay-3">
          <RouterLink to="/collection" class="luxury-btn-gold">Explore Collection</RouterLink>
        </div>
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
            v-for="(item, i) in featured"
            :key="item.title"
            to="/collection"
            v-reveal="i"
            class="media group relative block aspect-[4/5] overflow-hidden"
          >
            <div class="media-inner"></div>
            <div class="media-monogram">{{ item.letter }}</div>
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
              v-for="word in marquee"
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
          <div v-for="(stat, i) in stats" :key="stat.label" v-reveal="i" class="text-center">
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
            <p class="section-subtitle mb-6">Our Philosophy</p>
            <h2 class="section-title mb-8">Crafted with<br />Intention</h2>
            <p class="mb-6 leading-relaxed text-luxury-gray">
              We believe in the quiet power of exceptional craftsmanship. Every seam, every stitch, every detail is considered—not for show, but for the wearer.
            </p>
            <p class="mb-10 leading-relaxed text-luxury-gray">
              Our pieces are designed to transcend seasons, to become part of your story. This is slow fashion, in the truest sense.
            </p>
            <RouterLink to="/about" class="luxury-btn">Our Story</RouterLink>
          </div>
          <div v-reveal="1" class="media group relative aspect-square">
            <div class="media-inner"></div>
            <div class="media-monogram">M</div>
            <span class="media-sheen"></span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

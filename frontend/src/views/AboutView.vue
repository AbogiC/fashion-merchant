<script setup>
const values = [
  { number: '01', title: 'Heritage', description: 'Techniques passed down through generations, honoured in every piece.' },
  { number: '02', title: 'Craft', description: 'Hand-finished details that reveal themselves only on closer inspection.' },
  { number: '03', title: 'Sustainability', description: 'Natural fibres and considered production, made to last a lifetime.' },
]
</script>

<template>
  <div class="pt-20 md:pt-24">
    <section class="bg-luxury-cream py-24 md:py-32">
      <div class="luxury-container">
        <div v-reveal class="mx-auto mb-16 max-w-3xl text-center md:mb-24">
          <p class="section-subtitle mb-4">Our Story</p>
          <h1 class="section-title mb-8">About Maison</h1>
          <p class="text-lg leading-relaxed text-luxury-gray">
            Founded on the belief that true luxury is quiet, we create garments that are meant to be lived in—not just worn.
          </p>
        </div>

        <div class="grid grid-cols-1 items-center gap-16 md:grid-cols-2">
          <div v-reveal class="media group relative aspect-[3/4]">
            <div class="media-inner"></div>
            <div class="media-monogram">M</div>
            <span class="media-sheen"></span>
          </div>
          <div v-reveal="1">
            <h2 class="mb-6 text-2xl font-serif md:text-3xl">Craftsmanship as Philosophy</h2>
            <p class="mb-4 leading-relaxed text-luxury-gray">
              Every piece begins with the fabric. We source only the finest natural materials—cashmere from the highlands, linen from European mills, silk from heritage producers.
            </p>
            <p class="mb-4 leading-relaxed text-luxury-gray">
              Our atelier works with a small circle of artisans who share our commitment to excellence. Each garment is cut, sewn, and finished by hand, ensuring that no two pieces are exactly alike.
            </p>
            <p class="leading-relaxed text-luxury-gray">
              This is not fast fashion. This is a slower, more deliberate approach—one that honors the craft and the wearer in equal measure.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="border-t border-luxury-black/10 bg-luxury-light py-20 md:py-28">
      <div class="luxury-container">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-3 md:gap-12">
          <div
            v-for="(value, i) in values"
            :key="value.number"
            v-reveal="i"
            class="group border-t border-luxury-black/10 pt-8 transition-colors duration-500 hover:border-luxury-gold"
          >
            <p class="mb-4 text-xs tracking-luxury text-luxury-gold transition-transform duration-500 group-hover:translate-x-1.5">{{ value.number }}</p>
            <h3 class="mb-3 text-2xl font-serif transition-transform duration-500 group-hover:translate-x-1.5">{{ value.title }}</h3>
            <p class="leading-relaxed text-luxury-gray">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

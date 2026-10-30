<script setup>
import { RouterLink } from 'vue-router'

const year = new Date().getFullYear()

const explore = [
  { name: 'Collection', path: '/collection' },
  { name: 'Guidelines', path: '/guidelines' },
  { name: 'Mix & Match', path: '/mix-match' },
  { name: 'News', path: '/news' },
  { name: 'About Us', path: '/about' },
]

const social = ['Instagram', 'Pinterest', 'LinkedIn']
</script>

<template>
  <footer class="bg-luxury-black text-luxury-cream">
    <div class="luxury-container py-16 md:py-24">
      <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
        <div v-reveal class="lg:col-span-2">
          <RouterLink to="/" class="text-3xl font-serif tracking-luxury">MAISON</RouterLink>
          <p class="mt-6 max-w-sm text-sm leading-relaxed text-luxury-cream/60">
            Quiet luxury, crafted with intention. Timeless pieces designed to become part of your story.
          </p>
          <div class="mt-8 flex gap-6">
            <a
              v-for="item in social"
              :key="item"
              href="#"
              class="link-underline text-xs uppercase tracking-luxury text-luxury-cream/70 transition-colors duration-300 hover:text-luxury-gold"
            >
              {{ item }}
            </a>
          </div>
        </div>

        <div v-reveal="1">
          <h3 class="mb-6 text-xs uppercase tracking-luxury text-luxury-gold">Explore</h3>
          <ul class="space-y-3">
            <li v-for="link in explore" :key="link.path">
              <RouterLink
                :to="link.path"
                class="text-sm text-luxury-cream/70 transition-colors duration-300 hover:text-luxury-cream"
              >
                {{ link.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <div v-reveal="2">
          <h3 class="mb-6 text-xs uppercase tracking-luxury text-luxury-gold">Newsletter</h3>
          <p class="text-sm leading-relaxed text-luxury-cream/60">
            Join our circle for seasonal stories and private previews.
          </p>
          <form class="mt-4 flex border-b border-luxury-cream/20 transition-colors duration-300 focus-within:border-luxury-gold" @submit.prevent>
            <input
              type="email"
              placeholder="Your email"
              aria-label="Email address"
              class="w-full bg-transparent py-3 text-sm text-luxury-cream placeholder:text-luxury-cream/40 focus:outline-none"
            />
            <button type="submit" class="text-xs uppercase tracking-luxury text-luxury-gold transition-colors duration-300 hover:text-luxury-cream">
              Join
            </button>
          </form>
        </div>
      </div>

      <div class="mt-16 flex flex-col items-center justify-between gap-4 border-t border-luxury-cream/10 pt-8 md:flex-row">
        <p class="text-xs text-luxury-cream/40">© {{ year }} Maison. All rights reserved.</p>
        <p class="text-xs uppercase tracking-luxury text-luxury-cream/40">Crafted with intention</p>
      </div>
    </div>
  </footer>
</template>

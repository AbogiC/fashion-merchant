<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const isOpen = ref(false)
const scrolled = ref(false)
const accountOpen = ref(false)
const signingOut = ref(false)
const accountRef = ref(null)

const navLinks = [
  { name: 'Home', path: '/' },
  { name: 'Collection', path: '/collection' },
  { name: 'Guidelines', path: '/guidelines' },
  { name: 'Mix & Match', path: '/mix-match' },
  { name: 'News', path: '/news' },
  { name: 'About Us', path: '/about' },
]

const userInitials = computed(() => {
  const name = auth.user?.name?.trim()
  if (!name) return 'M'
  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0))
    .join('')
    .toUpperCase()
})

const toggleMenu = () => {
  isOpen.value = !isOpen.value
  accountOpen.value = false
}

const onScroll = () => {
  scrolled.value = window.scrollY > 16
}

const onClickOutside = (event) => {
  if (accountOpen.value && accountRef.value && !accountRef.value.contains(event.target)) {
    accountOpen.value = false
  }
}

const onKeydown = (event) => {
  if (event.key === 'Escape') {
    isOpen.value = false
    accountOpen.value = false
  }
}

async function handleLogout() {
  signingOut.value = true
  await auth.logout()
  signingOut.value = false
  accountOpen.value = false
  isOpen.value = false
  router.push('/')
}

watch(isOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

watch(
  () => route.fullPath,
  () => {
    isOpen.value = false
    accountOpen.value = false
  },
)

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('mousedown', onClickOutside)
  document.addEventListener('keydown', onKeydown)
  onScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('mousedown', onClickOutside)
  document.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <nav
    :class="[
      'fixed top-0 left-0 right-0 z-50 border-b transition-all duration-500',
      scrolled
        ? 'bg-luxury-cream/85 backdrop-blur-xl border-luxury-black/10 shadow-[0_8px_30px_-18px_rgba(10,10,10,.35)]'
        : 'bg-luxury-cream/40 backdrop-blur-sm border-transparent',
    ]"
  >
    <div class="luxury-container relative z-10">
      <div
        :class="[
          'flex items-center justify-between gap-4 transition-all duration-500',
          scrolled ? 'h-16 md:h-20' : 'h-20 md:h-24',
        ]"
      >
        <RouterLink
          to="/"
          class="group flex shrink-0 items-center text-2xl md:text-3xl font-serif tracking-luxury text-luxury-black"
          @click="isOpen = false"
        >
          <span class="transition-all duration-500 group-hover:tracking-[0.4em]">MAISON</span>
        </RouterLink>

        <nav class="hidden lg:flex items-center gap-6 xl:gap-9">
          <RouterLink
            v-for="link in navLinks"
            :key="link.name"
            :to="link.path"
            class="link-underline text-[11px] tracking-luxury uppercase text-luxury-black/70 transition-colors duration-300 hover:text-luxury-black"
          >
            {{ link.name }}
          </RouterLink>
        </nav>

        <div class="flex items-center gap-4">
          <div class="hidden lg:flex items-center gap-5">
            <template v-if="auth.isAuthenticated">
              <div ref="accountRef" class="relative">
                <button
                  type="button"
                  @click="accountOpen = !accountOpen"
                  class="group flex items-center gap-3"
                  aria-haspopup="menu"
                  :aria-expanded="accountOpen"
                  aria-label="Account menu"
                >
                  <span
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-luxury-gold/60 text-[11px] font-medium tracking-wide text-luxury-black transition-colors duration-300 group-hover:bg-luxury-gold/10"
                  >
                    {{ userInitials }}
                  </span>
                  <span class="hidden max-w-[8rem] truncate text-xs tracking-wide text-luxury-black/70 xl:block">
                    {{ auth.user.name }}
                  </span>
                  <svg
                    class="h-3 w-3 text-luxury-black/50 transition-transform duration-300"
                    :class="accountOpen ? 'rotate-180' : ''"
                    viewBox="0 0 12 12"
                    fill="none"
                    aria-hidden="true"
                  >
                    <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>

                <Transition name="dropdown">
                  <div
                    v-if="accountOpen"
                    class="absolute right-0 top-full z-50 mt-3 w-60 border border-luxury-black/10 bg-luxury-cream p-5 shadow-[0_24px_50px_-24px_rgba(10,10,10,.45)]"
                    role="menu"
                  >
                    <p class="text-[10px] uppercase tracking-luxury text-luxury-gray/70">Signed in as</p>
                    <p class="mt-2 truncate text-sm text-luxury-black">{{ auth.user.email }}</p>
                    <div class="my-4 h-px bg-luxury-black/10"></div>
                    <button
                      type="button"
                      @click="handleLogout"
                      :disabled="signingOut"
                      class="flex w-full items-center justify-between text-[11px] uppercase tracking-luxury text-luxury-black/70 transition-colors duration-300 hover:text-luxury-black disabled:opacity-50"
                      role="menuitem"
                    >
                      <span>{{ signingOut ? 'Signing out…' : 'Sign out' }}</span>
                      <span aria-hidden="true">→</span>
                    </button>
                  </div>
                </Transition>
              </div>
            </template>
            <template v-else>
              <RouterLink
                to="/login"
                class="link-underline text-[11px] uppercase tracking-luxury text-luxury-black/70 transition-colors duration-300 hover:text-luxury-black"
              >
                Sign In
              </RouterLink>
              <RouterLink to="/register" class="luxury-btn-gold nav-cta">Join</RouterLink>
            </template>
          </div>

          <button
            type="button"
            @click="toggleMenu"
            class="relative flex flex-col items-center justify-center space-y-1.5 p-2 lg:hidden"
            aria-label="Toggle menu"
            :aria-expanded="isOpen"
          >
            <span
              :class="['block h-px w-6 bg-luxury-black transition-all duration-300', isOpen ? 'rotate-45 translate-y-2' : '']"
            ></span>
            <span
              :class="['block h-px w-6 bg-luxury-black transition-all duration-300', isOpen ? 'opacity-0' : '']"
            ></span>
            <span
              :class="['block h-px w-6 bg-luxury-black transition-all duration-300', isOpen ? '-rotate-45 -translate-y-2' : '']"
            ></span>
          </button>
        </div>
      </div>
    </div>

    <div
      :class="[
        'menu-overlay fixed inset-x-0 top-0 z-0 h-[100dvh] lg:hidden',
        isOpen ? 'is-open opacity-100 visible' : 'opacity-0 invisible pointer-events-none',
      ]"
    >
      <div
        :class="[
          'flex h-full flex-col overflow-hidden bg-luxury-cream px-6 pb-[max(2rem,env(safe-area-inset-bottom))] transition-transform duration-500',
          scrolled ? 'pt-16 md:pt-20' : 'pt-20 md:pt-24',
          isOpen ? 'translate-y-0' : '-translate-y-3',
        ]"
      >
        <nav class="flex min-h-0 flex-1 flex-col items-center justify-center gap-5">
          <RouterLink
            v-for="(link, i) in navLinks"
            :key="link.name"
            :to="link.path"
            @click="isOpen = false"
            :class="[
              'text-2xl font-serif tracking-luxury text-luxury-black/80 transition-all duration-500 hover:text-luxury-black',
              isOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3',
            ]"
            :style="{ transitionDelay: isOpen ? `${i * 55 + 120}ms` : '0ms' }"
          >
            {{ link.name }}
          </RouterLink>
        </nav>

        <div
          :class="[
            'mx-auto w-full max-w-xs shrink-0 transition-all duration-500',
            isOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3',
          ]"
          :style="{ transitionDelay: isOpen ? `${navLinks.length * 55 + 140}ms` : '0ms' }"
        >
          <div class="mb-5 h-px w-full bg-luxury-black/10"></div>

          <template v-if="auth.isAuthenticated">
            <p class="mb-4 text-center text-xs text-luxury-gray">
              Signed in as <span class="break-all text-luxury-black">{{ auth.user.email }}</span>
            </p>
            <button
              type="button"
              @click="handleLogout"
              :disabled="signingOut"
              class="luxury-btn w-full text-center disabled:opacity-60"
            >
              {{ signingOut ? 'Signing out…' : 'Sign Out' }}
            </button>
          </template>
          <template v-else>
            <div class="flex flex-col gap-3">
              <RouterLink to="/login" @click="isOpen = false" class="luxury-btn w-full text-center">
                Sign In
              </RouterLink>
              <RouterLink to="/register" @click="isOpen = false" class="luxury-btn-gold w-full text-center">
                Create Account
              </RouterLink>
            </div>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.nav-cta {
  padding: 0.65rem 1.5rem;
  font-size: 11px;
}

.menu-overlay {
  transition: opacity 0.5s ease, visibility 0s linear 0.5s;
}
.menu-overlay.is-open {
  transition: opacity 0.5s ease, visibility 0s linear 0s;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>

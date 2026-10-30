<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)
const submitting = ref(false)
const errors = ref({})

async function onSubmit() {
  errors.value = {}
  submitting.value = true
  try {
    await auth.register({
      name: form.name.trim(),
      email: form.email.trim(),
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    router.push('/')
  } catch (e) {
    errors.value = e.errors || {}
    if (Object.keys(errors.value).length === 0) {
      errors.value = { form: e.message || 'Unable to create your account.' }
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-stretch bg-luxury-cream pt-20 md:pt-24">
    <!-- Form panel -->
    <main class="flex w-full items-center justify-center px-6 py-16 lg:order-1 lg:w-1/2">
      <div class="w-full max-w-md animate-fade-up">
        <RouterLink to="/" class="mb-12 block text-center text-2xl font-serif tracking-[0.4em] text-luxury-black lg:hidden">
          MAISON
        </RouterLink>

        <p class="section-subtitle mb-4 text-center">Become a Member</p>
        <h1 class="section-title mb-2 text-center">Create Account</h1>
        <p class="mb-10 text-center text-sm text-luxury-gray">Join the Maison for an elevated experience.</p>

        <form @submit.prevent="onSubmit" novalidate class="space-y-7">
          <Transition name="page">
            <p v-if="errors.form" class="rounded-sm border border-red-300/50 bg-red-50/60 px-4 py-3 text-xs tracking-wide text-red-700">
              {{ errors.form }}
            </p>
          </Transition>

          <!-- Name -->
          <div class="auth-field">
            <input
              id="name"
              v-model.trim="form.name"
              type="text"
              placeholder=" "
              autocomplete="name"
              required
              class="auth-input"
              :class="{ 'border-red-400': errors.name }"
            />
            <label for="name" class="auth-label">Full name</label>
            <p v-if="errors.name" class="mt-2 text-xs text-red-600">{{ errors.name[0] }}</p>
          </div>

          <!-- Email -->
          <div class="auth-field">
            <input
              id="email"
              v-model.trim="form.email"
              type="email"
              placeholder=" "
              autocomplete="email"
              required
              class="auth-input"
              :class="{ 'border-red-400': errors.email }"
            />
            <label for="email" class="auth-label">Email address</label>
            <p v-if="errors.email" class="mt-2 text-xs text-red-600">{{ errors.email[0] }}</p>
          </div>

          <!-- Password -->
          <div class="auth-field">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder=" "
              autocomplete="new-password"
              required
              class="auth-input"
              :class="{ 'border-red-400': errors.password }"
            />
            <label for="password" class="auth-label">Password</label>
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="auth-input-icon"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
              tabindex="-1"
            >
              {{ showPassword ? '◑' : '◯' }}
            </button>
            <p v-if="errors.password" class="mt-2 text-xs text-red-600">{{ errors.password[0] }}</p>
          </div>

          <!-- Confirm password -->
          <div class="auth-field">
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              placeholder=" "
              autocomplete="new-password"
              required
              class="auth-input"
              :class="{ 'border-red-400': errors.password }"
            />
            <label for="password_confirmation" class="auth-label">Confirm password</label>
          </div>

          <button type="submit" :disabled="submitting" class="luxury-btn-gold w-full disabled:cursor-not-allowed disabled:opacity-60">
            <span v-if="submitting">Creating account…</span>
            <span v-else>Create Account</span>
          </button>
        </form>

        <p class="mt-10 text-center text-sm text-luxury-gray">
          Already a member?
          <RouterLink to="/login" class="link-underline font-medium text-luxury-black">Sign in</RouterLink>
        </p>
      </div>
    </main>

    <!-- Brand panel -->
    <aside class="relative hidden w-1/2 overflow-hidden bg-luxury-black lg:order-2 lg:flex">
      <div class="hero-bg absolute inset-0"></div>
      <div class="hero-orb animate-float absolute -right-1/4 top-1/4 h-96 w-96 rounded-full blur-3xl"></div>
      <div class="hero-orb absolute -left-1/4 bottom-1/4 h-80 w-80 rounded-full blur-3xl" style="animation: float 9s ease-in-out infinite;"></div>
      <div class="relative z-10 flex w-full flex-col justify-between p-16">
        <RouterLink to="/" class="text-2xl font-serif tracking-[0.4em] text-luxury-cream">
          MAISON
        </RouterLink>
        <div>
          <p class="mb-4 text-xs uppercase tracking-luxury text-luxury-gold">Membership</p>
          <h2 class="text-4xl font-serif font-light leading-tight text-luxury-cream xl:text-5xl">
            Step into<br />the Maison.
          </h2>
          <p class="mt-6 max-w-sm text-sm font-light leading-relaxed text-luxury-cream/60">
            Create an account to save your edit, receive private invitations and shop the collection first.
          </p>
        </div>
        <p class="text-xs uppercase tracking-luxury text-luxury-cream/30">By invitation only</p>
      </div>
    </aside>
  </div>
</template>

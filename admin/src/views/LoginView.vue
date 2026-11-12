<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { ApiError } from '@/api/client'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import FormField from '@/components/ui/FormField.vue'
import GoogleAuthButton from '@/components/GoogleAuthButton.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const email = ref('')
const password = ref('')
const submitting = ref(false)

function initialError() {
  if (route.query.error === 'admin') {
    return 'This account does not have administrator access.'
  }
  if (route.query.oauth === 'error') {
    return typeof route.query.message === 'string'
      ? route.query.message
      : 'Unable to sign in with Google. Please try again.'
  }
  return ''
}

const error = ref(initialError())

async function submit() {
  error.value = ''
  if (!email.value || !password.value) {
    error.value = 'Enter your email and password.'
    return
  }

  submitting.value = true
  try {
    await auth.login({ email: email.value, password: password.value })

    if (!auth.isAdmin) {
      error.value = 'This account does not have administrator access.'
      await auth.logout()
      return
    }

    toast.success('Welcome back')
    const redirect = route.query.redirect
    router.push(typeof redirect === 'string' ? redirect : { name: 'dashboard' })
  } catch (e) {
    error.value = e instanceof ApiError ? e.message : 'Unable to sign in. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="auth-screen">
    <div class="auth-card">
      <div class="auth-card__brand">
        <div class="auth-card__name">Fashion Merchant</div>
        <div class="auth-card__sub">Admin Console</div>
      </div>

      <div v-if="error" class="auth-alert">{{ error }}</div>

      <GoogleAuthButton style="margin-bottom: 18px" />

      <div class="auth-divider">or continue with email</div>

      <form @submit.prevent="submit">
        <FormField label="Email">
          <BaseInput
            v-model="email"
            type="email"
            placeholder="you@example.com"
            autocomplete="username"
          />
        </FormField>
        <FormField label="Password">
          <BaseInput
            v-model="password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
          />
        </FormField>
        <BaseButton variant="gold" block :loading="submitting" type="submit" style="margin-top: 4px">
          Sign in
        </BaseButton>
      </form>
    </div>
  </div>
</template>

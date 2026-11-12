import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { authApi } from '@/api/auth'

/**
 * Admin auth store.
 *
 * Tokens are HttpOnly so the store cannot read them; it keeps only the user
 * object in memory and hydrates it from /auth/me on app start. `isAdmin`
 * gates the protected routes — the backend AdminMiddleware is the real
 * authority, this is just for UX/redirects.
 */
export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const hydrated = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => (user.value?.role ?? 'user') === 'admin')

  async function fetchUser() {
    try {
      const res = await authApi.me()
      user.value = res.user
      return true
    } catch {
      user.value = null
      return false
    } finally {
      hydrated.value = true
    }
  }

  async function login(credentials) {
    loading.value = true
    try {
      const res = await authApi.login(credentials)
      user.value = res.user
      hydrated.value = true
      return res
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {
      // Drop the local session even if the call fails.
    } finally {
      user.value = null
    }
  }

  return { user, loading, hydrated, isAuthenticated, isAdmin, fetchUser, login, logout }
})

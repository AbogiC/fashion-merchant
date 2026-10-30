import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { api } from '@/api/client'

/**
 * Auth store.
 *
 * Because tokens live in HttpOnly cookies the SPA cannot read them, so this
 * store only keeps the user object in memory and hydrates it from /auth/me.
 * `hydrated` records whether we have already attempted to load the session,
 * which the router guard uses to avoid a redirect flicker on refresh.
 */
export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const hydrated = ref(false)

  const isAuthenticated = computed(() => !!user.value)

  /** Load the current user from the API (used on app start and refresh). */
  async function fetchUser() {
    try {
      const res = await api.get('/auth/me')
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
      const res = await api.post('/auth/login', credentials)
      user.value = res.user
      hydrated.value = true
      return res
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    try {
      const res = await api.post('/auth/register', payload)
      user.value = res.user
      hydrated.value = true
      return res
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // Even if the call fails, drop the local session.
    } finally {
      user.value = null
    }
  }

  return { user, loading, hydrated, isAuthenticated, fetchUser, login, register, logout }
})

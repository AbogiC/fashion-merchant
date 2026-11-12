import { api } from './client'

/**
 * Auth endpoints. Tokens live in HttpOnly cookies set by the backend, so the
 * SPA only manages the user object returned from these calls in memory.
 */
export const authApi = {
  login: (credentials) => api.post('/auth/login', credentials),
  me: () => api.get('/auth/me'),
  logout: () => api.post('/auth/logout'),
}

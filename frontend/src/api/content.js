import { api } from './client'

/**
 * Public content endpoints. Each page loads its own slice so content can be
 * managed from the database without shipping hardcoded copy in the SPA.
 */
export const contentApi = {
  home: () => api.get('/content/home'),
  collections: () => api.get('/collections'),
  guidelines: () => api.get('/guidelines'),
  mixMatch: () => api.get('/mix-match'),
  news: () => api.get('/news'),
}

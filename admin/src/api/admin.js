import { api } from './client'

/**
 * Admin content-management endpoints. Each resource supports a list call and
 * full create/update/delete. The Home page is a composite of a settings row
 * plus three sub-resources (featured, stats, marquee).
 */
export const adminApi = {
  // Collections
  listCollections: () => api.get('/admin/collections'),
  createCollection: (body) => api.post('/admin/collections', body),
  updateCollection: (id, body) => api.put(`/admin/collections/${id}`, body),
  deleteCollection: (id) => api.delete(`/admin/collections/${id}`),

  // Guidelines
  listGuidelines: () => api.get('/admin/guidelines'),
  createGuideline: (body) => api.post('/admin/guidelines', body),
  updateGuideline: (id, body) => api.put(`/admin/guidelines/${id}`, body),
  deleteGuideline: (id) => api.delete(`/admin/guidelines/${id}`),

  // Looks (Mix & Match)
  listLooks: () => api.get('/admin/looks'),
  createLook: (body) => api.post('/admin/looks', body),
  updateLook: (id, body) => api.put(`/admin/looks/${id}`, body),
  deleteLook: (id) => api.delete(`/admin/looks/${id}`),

  // News
  listNews: () => api.get('/admin/news'),
  createNews: (body) => api.post('/admin/news', body),
  updateNews: (id, body) => api.put(`/admin/news/${id}`, body),
  deleteNews: (id) => api.delete(`/admin/news/${id}`),

  // Home
  getHome: () => api.get('/admin/home'),
  updateHomeSettings: (body) => api.put('/admin/home/settings', body),

  createFeatured: (body) => api.post('/admin/home/featured', body),
  updateFeatured: (id, body) => api.put(`/admin/home/featured/${id}`, body),
  deleteFeatured: (id) => api.delete(`/admin/home/featured/${id}`),

  createStat: (body) => api.post('/admin/home/stats', body),
  updateStat: (id, body) => api.put(`/admin/home/stats/${id}`, body),
  deleteStat: (id) => api.delete(`/admin/home/stats/${id}`),

  createMarquee: (body) => api.post('/admin/home/marquee', body),
  updateMarquee: (id, body) => api.put(`/admin/home/marquee/${id}`, body),
  deleteMarquee: (id) => api.delete(`/admin/home/marquee/${id}`),
}

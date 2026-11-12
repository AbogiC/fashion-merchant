import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresGuest: true, title: 'Sign in' },
  },
  {
    path: '/',
    component: () => import('../components/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'home',
        name: 'home',
        component: () => import('../views/HomeEditorView.vue'),
        meta: { title: 'Home Page' },
      },
      {
        path: 'collections',
        name: 'collections',
        component: () => import('../views/CollectionsView.vue'),
        meta: { title: 'Collections' },
      },
      {
        path: 'guidelines',
        name: 'guidelines',
        component: () => import('../views/GuidelinesView.vue'),
        meta: { title: 'Guidelines' },
      },
      {
        path: 'mix-match',
        name: 'mix-match',
        component: () => import('../views/MixMatchView.vue'),
        meta: { title: 'Mix & Match' },
      },
      {
        path: 'news',
        name: 'news',
        component: () => import('../views/NewsView.vue'),
        meta: { title: 'News' },
      },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

/**
 * Hydrate the session once (tokens are HttpOnly so the store starts empty
 * after a refresh), then enforce the auth/meta guards.
 */
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.hydrated) {
    await auth.fetchUser()
  }

  if (to.meta.requiresGuest && auth.isAdmin) {
    return { name: 'dashboard' }
  }
  if (to.meta.requiresAuth && !auth.isAdmin) {
    const wasAuthenticated = auth.isAuthenticated
    if (wasAuthenticated) {
      await auth.logout()
    }
    return {
      name: 'login',
      query: wasAuthenticated
        ? { redirect: to.fullPath, error: 'admin' }
        : { redirect: to.fullPath },
    }
  }

  return true
})

export default router

import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/collection',
      name: 'collection',
      component: () => import('../views/CollectionView.vue')
    },
    {
      path: '/guidelines',
      name: 'guidelines',
      component: () => import('../views/GuidelinesView.vue')
    },
    {
      path: '/mix-match',
      name: 'mix-match',
      component: () => import('../views/MixMatchView.vue')
    },
    {
      path: '/news',
      name: 'news',
      component: () => import('../views/NewsView.vue')
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { requiresGuest: true }
    }
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

/**
 * Global guard. On the first navigation we hydrate the session from the API
 * (tokens are HttpOnly so the store starts empty after a refresh). Then we
 * enforce route meta guards for guest-only and (future) auth-only pages.
 */
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.hydrated) {
    await auth.fetchUser()
  }

  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return { name: 'home' }
  }
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  return true
})

export default router

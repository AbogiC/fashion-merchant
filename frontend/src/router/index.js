import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

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
    }
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

export default router
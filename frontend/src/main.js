import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { reveal } from './directives/reveal'
import './assets/main.css'

const app = createApp(App)

app.directive('reveal', reveal)
app.use(createPinia())
app.use(router)

app.mount('#app')

/**
 * Dismiss the pre-render splash (#app-loader) once the initial route has
 * resolved — which includes the auth hydration call in the global guard —
 * and a minimum dwell time has elapsed so the brand moment can breathe.
 *
 * A hard cap guards against a hanging/unreachable backend so the UI never
 * stays locked on the splash.
 */
const MIN_DWELL = 1500
const MAX_WAIT = 5000

const dwell = new Promise((resolve) => setTimeout(resolve, MIN_DWELL))
const ready = router.isReady().catch(() => {})
const maxWait = new Promise((resolve) => setTimeout(resolve, MAX_WAIT))

Promise.race([Promise.all([ready, dwell]), maxWait]).then(() => {
  const loader = document.getElementById('app-loader')
  if (!loader) return
  loader.classList.add('app-loader--done')
  const remove = () => loader.remove()
  loader.addEventListener('transitionend', remove, { once: true })
  // Safety net if transitionend never fires.
  setTimeout(remove, 1200)
})

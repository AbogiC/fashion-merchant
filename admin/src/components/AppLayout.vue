<script setup>
import { ref } from 'vue'
import { RouterView } from 'vue-router'
import { useRouter } from 'vue-router'
import AppSidebar from './AppSidebar.vue'
import AppTopbar from './AppTopbar.vue'
import BaseButton from './ui/BaseButton.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const sidebarOpen = ref(false)

async function handleLogout() {
  await auth.logout()
  toast.info('Signed out')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app-shell">
    <AppSidebar :open="sidebarOpen" @close="sidebarOpen = false" />
    <div class="app-main">
      <AppTopbar @menu="sidebarOpen = true">
        <template #actions>
          <BaseButton variant="ghost" size="sm" @click="handleLogout">Sign out</BaseButton>
        </template>
      </AppTopbar>
      <main class="app-content">
        <RouterView v-slot="{ Component }">
          <component :is="Component" />
        </RouterView>
      </main>
    </div>
  </div>
</template>

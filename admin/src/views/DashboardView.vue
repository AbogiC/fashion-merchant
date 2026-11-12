<script setup>
import { onMounted, ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/ui/BaseButton.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const auth = useAuthStore()

const loading = ref(true)
const error = ref('')
const data = ref({
  collections: [],
  guidelines: [],
  looks: [],
  news: [],
  home: { featured: [], stats: [], marquee: [] },
})

const activeCount = (arr) => arr.filter((x) => x.is_active).length

const cards = computed(() => [
  {
    label: 'Collections',
    value: data.value.collections.length,
    foot: `${activeCount(data.value.collections)} published`,
    to: { name: 'collections' },
  },
  {
    label: 'Guidelines',
    value: data.value.guidelines.length,
    foot: `${activeCount(data.value.guidelines)} published`,
    to: { name: 'guidelines' },
  },
  {
    label: 'Mix & Match',
    value: data.value.looks.length,
    foot: `${activeCount(data.value.looks)} published`,
    to: { name: 'mix-match' },
  },
  {
    label: 'News stories',
    value: data.value.news.length,
    foot: `${activeCount(data.value.news)} published`,
    to: { name: 'news' },
  },
  {
    label: 'Featured cards',
    value: data.value.home.featured.length,
    foot: 'On the home page',
    to: { name: 'home' },
  },
  {
    label: 'Marquee words',
    value: data.value.home.marquee.length,
    foot: 'On the home page',
    to: { name: 'home' },
  },
])

onMounted(async () => {
  try {
    const [c, g, l, n, h] = await Promise.all([
      adminApi.listCollections(),
      adminApi.listGuidelines(),
      adminApi.listLooks(),
      adminApi.listNews(),
      adminApi.getHome(),
    ])
    data.value = {
      collections: c.collections ?? [],
      guidelines: g.guidelines ?? [],
      looks: l.looks ?? [],
      news: n.news ?? [],
      home: h,
    }
  } catch (e) {
    error.value = e.message || 'Unable to load the overview.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <div class="page-head">
      <h1 class="page-head__title">Dashboard</h1>
      <p class="page-head__desc">
        Welcome back, {{ auth.user?.name }}. Manage the content shown across the guest site.
      </p>
    </div>

    <div v-if="loading" class="loading-block"><LoadingSpinner dark /> Loading the overview…</div>
    <p v-else-if="error" class="text-danger">{{ error }}</p>

    <template v-else>
      <div class="stat-grid">
        <RouterLink v-for="c in cards" :key="c.label" :to="c.to" class="stat-card">
          <div class="stat-card__label">{{ c.label }}</div>
          <div class="stat-card__value">{{ c.value }}</div>
          <div class="stat-card__foot">{{ c.foot }}</div>
        </RouterLink>
      </div>

      <div class="panel mt-6">
        <div class="panel__head">
          <h2 class="panel__title">Quick actions</h2>
        </div>
        <div class="panel__body row row--wrap">
          <BaseButton variant="ghost" @click="$router.push({ name: 'home' })">Edit home page</BaseButton>
          <BaseButton variant="ghost" @click="$router.push({ name: 'collections' })">Manage collections</BaseButton>
          <BaseButton variant="ghost" @click="$router.push({ name: 'guidelines' })">Manage guidelines</BaseButton>
          <BaseButton variant="ghost" @click="$router.push({ name: 'mix-match' })">Manage looks</BaseButton>
          <BaseButton variant="ghost" @click="$router.push({ name: 'news' })">Manage news</BaseButton>
        </div>
      </div>
    </template>
  </div>
</template>

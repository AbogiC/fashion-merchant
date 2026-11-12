<script setup>
import { onMounted, reactive, ref } from 'vue'
import { adminApi } from '@/api/admin'
import { useToast } from '@/composables/useToast'
import { ApiError } from '@/api/client'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseTextarea from '@/components/ui/BaseTextarea.vue'
import BaseToggle from '@/components/ui/BaseToggle.vue'
import FormField from '@/components/ui/FormField.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const toast = useToast()

const home = ref({ settings: {}, featured: [], stats: [], marquee: [] })
const loading = ref(true)
const error = ref('')

// ---- Settings form (hero + philosophy) ----
const settings = reactive({})
const settingsErrors = ref({})
const savingSettings = ref(false)

// ---- Featured ----
const featuredModal = ref(false)
const featuredSaving = ref(false)
const featuredForm = reactive(blankFeatured())
const featuredErrors = ref({})
const featuredDelete = ref(null)
const featuredDeleting = ref(false)

// ---- Stats ----
const statsModal = ref(false)
const statsSaving = ref(false)
const statsForm = reactive(blankStat())
const statsErrors = ref({})
const statsDelete = ref(null)
const statsDeleting = ref(false)

// ---- Marquee ----
const marqueeModal = ref(false)
const marqueeSaving = ref(false)
const marqueeForm = reactive(blankMarquee())
const marqueeErrors = ref({})
const marqueeDelete = ref(null)
const marqueeDeleting = ref(false)

function blankFeatured() {
  return { tag: '', title: '', copy: '', monogram: '', image_url: '', link_url: '/collection', sort_order: 0, is_active: true }
}
function blankStat() {
  return { value: '', suffix: '', label: '', sort_order: 0, is_active: true }
}
function blankMarquee() {
  return { word: '', sort_order: 0, is_active: true }
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await adminApi.getHome()
    home.value = res
    Object.assign(settings, res.settings ?? {})
  } catch (e) {
    error.value = e.message || 'Unable to load the home page content.'
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  savingSettings.value = true
  settingsErrors.value = {}
  try {
    const res = await adminApi.updateHomeSettings({ ...settings })
    Object.assign(settings, res.settings ?? settings)
    toast.success('Home page updated')
  } catch (e) {
    if (e instanceof ApiError && e.errors) settingsErrors.value = e.errors
    else toast.error('Save failed', e.message)
  } finally {
    savingSettings.value = false
  }
}

// ---- Featured CRUD ----
function openFeaturedCreate() {
  Object.assign(featuredForm, blankFeatured())
  featuredForm._id = null
  featuredErrors.value = {}
  featuredModal.value = true
}
function openFeaturedEdit(item) {
  Object.assign(featuredForm, { ...blankFeatured(), ...item, _id: item.id })
  featuredErrors.value = {}
  featuredModal.value = true
}
async function saveFeatured() {
  featuredSaving.value = true
  try {
    const id = featuredForm._id
    const payload = { ...featuredForm }
    delete payload._id
    if (id == null) {
      await adminApi.createFeatured(payload)
      toast.success('Featured card added')
    } else {
      await adminApi.updateFeatured(id, payload)
      toast.success('Featured card updated')
    }
    featuredModal.value = false
    await load()
  } catch (e) {
    if (e instanceof ApiError && e.errors) featuredErrors.value = e.errors
    else toast.error('Save failed', e.message)
  } finally {
    featuredSaving.value = false
  }
}
async function confirmFeaturedDelete() {
  if (!featuredDelete.value) return
  featuredDeleting.value = true
  try {
    await adminApi.deleteFeatured(featuredDelete.value.id)
    toast.success('Featured card deleted')
    featuredDelete.value = null
    await load()
  } catch (e) {
    toast.error('Delete failed', e.message)
  } finally {
    featuredDeleting.value = false
  }
}

// ---- Stats CRUD ----
function openStatCreate() {
  Object.assign(statsForm, blankStat())
  statsForm._id = null
  statsErrors.value = {}
  statsModal.value = true
}
function openStatEdit(item) {
  Object.assign(statsForm, { ...blankStat(), ...item, _id: item.id })
  statsErrors.value = {}
  statsModal.value = true
}
async function saveStat() {
  statsSaving.value = true
  try {
    const id = statsForm._id
    const payload = { ...statsForm }
    delete payload._id
    if (id == null) {
      await adminApi.createStat(payload)
      toast.success('Stat added')
    } else {
      await adminApi.updateStat(id, payload)
      toast.success('Stat updated')
    }
    statsModal.value = false
    await load()
  } catch (e) {
    if (e instanceof ApiError && e.errors) statsErrors.value = e.errors
    else toast.error('Save failed', e.message)
  } finally {
    statsSaving.value = false
  }
}
async function confirmStatDelete() {
  if (!statsDelete.value) return
  statsDeleting.value = true
  try {
    await adminApi.deleteStat(statsDelete.value.id)
    toast.success('Stat deleted')
    statsDelete.value = null
    await load()
  } catch (e) {
    toast.error('Delete failed', e.message)
  } finally {
    statsDeleting.value = false
  }
}

// ---- Marquee CRUD ----
function openMarqueeCreate() {
  Object.assign(marqueeForm, blankMarquee())
  marqueeForm._id = null
  marqueeErrors.value = {}
  marqueeModal.value = true
}
function openMarqueeEdit(item) {
  Object.assign(marqueeForm, { ...blankMarquee(), ...item, _id: item.id })
  marqueeErrors.value = {}
  marqueeModal.value = true
}
async function saveMarquee() {
  marqueeSaving.value = true
  try {
    const id = marqueeForm._id
    const payload = { ...marqueeForm }
    delete payload._id
    if (id == null) {
      await adminApi.createMarquee(payload)
      toast.success('Marquee word added')
    } else {
      await adminApi.updateMarquee(id, payload)
      toast.success('Marquee word updated')
    }
    marqueeModal.value = false
    await load()
  } catch (e) {
    if (e instanceof ApiError && e.errors) marqueeErrors.value = e.errors
    else toast.error('Save failed', e.message)
  } finally {
    marqueeSaving.value = false
  }
}
async function confirmMarqueeDelete() {
  if (!marqueeDelete.value) return
  marqueeDeleting.value = true
  try {
    await adminApi.deleteMarquee(marqueeDelete.value.id)
    toast.success('Marquee word deleted')
    marqueeDelete.value = null
    await load()
  } catch (e) {
    toast.error('Delete failed', e.message)
  } finally {
    marqueeDeleting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-head">
      <h1 class="page-head__title">Home Page</h1>
      <p class="page-head__desc">The hero, philosophy, featured cards, stats and marquee shown on the landing page.</p>
    </div>

    <div v-if="loading" class="loading-block"><LoadingSpinner dark /> Loading…</div>
    <p v-else-if="error" class="text-danger">{{ error }}</p>

    <template v-else>
      <!-- Hero & Philosophy settings -->
      <div class="panel">
        <div class="panel__head">
          <h2 class="panel__title">Hero &amp; Philosophy</h2>
        </div>
        <div class="panel__body">
          <h3 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; margin: 0 0 14px">Hero section</h3>
          <div class="form-grid">
            <FormField label="Eyebrow" :error="settingsErrors.hero_eyebrow">
              <BaseInput v-model="settings.hero_eyebrow" :invalid="!!settingsErrors.hero_eyebrow" />
            </FormField>
            <FormField label="CTA label" :error="settingsErrors.hero_cta_label">
              <BaseInput v-model="settings.hero_cta_label" :invalid="!!settingsErrors.hero_cta_label" />
            </FormField>
            <FormField label="Title (line 1)" :error="settingsErrors.hero_title_primary">
              <BaseInput v-model="settings.hero_title_primary" :invalid="!!settingsErrors.hero_title_primary" />
            </FormField>
            <FormField label="Title (line 2)" :error="settingsErrors.hero_title_secondary">
              <BaseInput v-model="settings.hero_title_secondary" :invalid="!!settingsErrors.hero_title_secondary" />
            </FormField>
            <div class="col-span-2">
              <FormField label="Description" :error="settingsErrors.hero_description">
                <BaseTextarea v-model="settings.hero_description" :invalid="!!settingsErrors.hero_description" rows="3" />
              </FormField>
            </div>
            <FormField label="CTA URL" :error="settingsErrors.hero_cta_url">
              <BaseInput v-model="settings.hero_cta_url" :invalid="!!settingsErrors.hero_cta_url" placeholder="/collection" />
            </FormField>
          </div>

          <h3 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; margin: 24px 0 14px">Philosophy section</h3>
          <div class="form-grid">
            <FormField label="Subtitle" :error="settingsErrors.philosophy_subtitle">
              <BaseInput v-model="settings.philosophy_subtitle" :invalid="!!settingsErrors.philosophy_subtitle" />
            </FormField>
            <FormField label="Monogram" :error="settingsErrors.philosophy_monogram">
              <BaseInput v-model="settings.philosophy_monogram" :invalid="!!settingsErrors.philosophy_monogram" />
            </FormField>
            <FormField label="Title (line 1)" :error="settingsErrors.philosophy_title_primary">
              <BaseInput v-model="settings.philosophy_title_primary" :invalid="!!settingsErrors.philosophy_title_primary" />
            </FormField>
            <FormField label="Title (line 2)" :error="settingsErrors.philosophy_title_secondary">
              <BaseInput v-model="settings.philosophy_title_secondary" :invalid="!!settingsErrors.philosophy_title_secondary" />
            </FormField>
            <div class="col-span-2">
              <FormField label="Body" hint="Separate paragraphs with a blank line." :error="settingsErrors.philosophy_body">
                <BaseTextarea v-model="settings.philosophy_body" :invalid="!!settingsErrors.philosophy_body" rows="5" />
              </FormField>
            </div>
            <FormField label="CTA label" :error="settingsErrors.philosophy_cta_label">
              <BaseInput v-model="settings.philosophy_cta_label" :invalid="!!settingsErrors.philosophy_cta_label" />
            </FormField>
            <FormField label="CTA URL" :error="settingsErrors.philosophy_cta_url">
              <BaseInput v-model="settings.philosophy_cta_url" :invalid="!!settingsErrors.philosophy_cta_url" placeholder="/about" />
            </FormField>
          </div>
        </div>
        <div class="card__foot">
          <BaseButton variant="gold" :loading="savingSettings" @click="saveSettings">Save home page</BaseButton>
        </div>
      </div>

      <!-- Featured cards -->
      <div class="panel">
        <div class="panel__head">
          <h2 class="panel__title">Featured cards</h2>
          <BaseButton variant="ghost" size="sm" @click="openFeaturedCreate">+ Add card</BaseButton>
        </div>
        <div v-if="home.featured.length === 0" class="panel__body">
          <EmptyState title="No featured cards" message="Add cards to the curated edit on the home page." />
        </div>
        <div v-else class="table-wrap">
          <table class="table">
            <thead>
              <tr><th>Tag</th><th>Title</th><th>Copy</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
              <tr v-for="item in home.featured" :key="item.id">
                <td class="text-muted nowrap">{{ item.tag || '—' }}</td>
                <td class="table__cell-title">{{ item.title }}</td>
                <td class="table__cell-sub truncate" style="max-width: 260px">{{ item.copy }}</td>
                <td>{{ item.sort_order }}</td>
                <td><span class="badge" :class="item.is_active ? 'badge--on' : 'badge--off'"><span class="badge__dot"></span>{{ item.is_active ? 'Published' : 'Draft' }}</span></td>
                <td class="table__actions">
                  <BaseButton variant="ghost" size="sm" @click="openFeaturedEdit(item)">Edit</BaseButton>
                  <BaseButton variant="danger" size="sm" @click="featuredDelete = item">Delete</BaseButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stats -->
      <div class="panel">
        <div class="panel__head">
          <h2 class="panel__title">Stats</h2>
          <BaseButton variant="ghost" size="sm" @click="openStatCreate">+ Add stat</BaseButton>
        </div>
        <div v-if="home.stats.length === 0" class="panel__body">
          <EmptyState title="No stats" message="Add figures to the dark stats strip on the home page." />
        </div>
        <div v-else class="table-wrap">
          <table class="table">
            <thead>
              <tr><th>Value</th><th>Suffix</th><th>Label</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
              <tr v-for="item in home.stats" :key="item.id">
                <td class="table__cell-title">{{ item.value }}</td>
                <td class="text-muted">{{ item.suffix || '—' }}</td>
                <td>{{ item.label }}</td>
                <td>{{ item.sort_order }}</td>
                <td><span class="badge" :class="item.is_active ? 'badge--on' : 'badge--off'"><span class="badge__dot"></span>{{ item.is_active ? 'Published' : 'Draft' }}</span></td>
                <td class="table__actions">
                  <BaseButton variant="ghost" size="sm" @click="openStatEdit(item)">Edit</BaseButton>
                  <BaseButton variant="danger" size="sm" @click="statsDelete = item">Delete</BaseButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Marquee -->
      <div class="panel">
        <div class="panel__head">
          <h2 class="panel__title">Marquee words</h2>
          <BaseButton variant="ghost" size="sm" @click="openMarqueeCreate">+ Add word</BaseButton>
        </div>
        <div class="panel__body">
          <EmptyState v-if="home.marquee.length === 0" title="No marquee words" message="Add words to the scrolling strip on the home page." />
          <div v-else class="chip-list">
            <span
              v-for="item in home.marquee"
              :key="item.id"
              class="chip"
              :class="{ 'is-off': !item.is_active }"
            >
              <span class="nav-item__icon" style="cursor: pointer" @click="openMarqueeEdit(item)">{{ item.word }}</span>
              <button class="chip__remove" aria-label="Remove" @click="marqueeDelete = item">&times;</button>
            </span>
          </div>
        </div>
      </div>
    </template>

    <!-- Featured modal -->
    <BaseModal v-model="featuredModal" :title="featuredForm._id == null ? 'New featured card' : 'Edit featured card'">
      <div class="form-grid">
        <FormField label="Tag" hint="Small gold eyebrow text" :error="featuredErrors.tag">
          <BaseInput v-model="featuredForm.tag" :invalid="!!featuredErrors.tag" />
        </FormField>
        <FormField label="Title" :error="featuredErrors.title">
          <BaseInput v-model="featuredForm.title" :invalid="!!featuredErrors.title" />
        </FormField>
        <div class="col-span-2">
          <FormField label="Copy" :error="featuredErrors.copy">
            <BaseInput v-model="featuredForm.copy" :invalid="!!featuredErrors.copy" placeholder="Short hover caption" />
          </FormField>
        </div>
        <FormField label="Monogram" :error="featuredErrors.monogram">
          <BaseInput v-model="featuredForm.monogram" :invalid="!!featuredErrors.monogram" />
        </FormField>
        <FormField label="Image URL" :error="featuredErrors.image_url">
          <BaseInput v-model="featuredForm.image_url" :invalid="!!featuredErrors.image_url" placeholder="https://…" />
        </FormField>
        <FormField label="Link URL" :error="featuredErrors.link_url">
          <BaseInput v-model="featuredForm.link_url" :invalid="!!featuredErrors.link_url" />
        </FormField>
        <FormField label="Sort order" :error="featuredErrors.sort_order">
          <BaseInput v-model="featuredForm.sort_order" type="number" :invalid="!!featuredErrors.sort_order" />
        </FormField>
        <div class="col-span-2">
          <FormField label="Published">
            <BaseToggle v-model="featuredForm.is_active" label="Show on the home page" />
          </FormField>
        </div>
      </div>
      <template #foot>
        <BaseButton variant="ghost" @click="featuredModal = false">Cancel</BaseButton>
        <BaseButton variant="gold" :loading="featuredSaving" @click="saveFeatured">
          {{ featuredForm._id == null ? 'Create' : 'Save changes' }}
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Stat modal -->
    <BaseModal v-model="statsModal" :title="statsForm._id == null ? 'New stat' : 'Edit stat'">
      <div class="form-grid">
        <FormField label="Value" hint="e.g. 120" :error="statsErrors.value">
          <BaseInput v-model="statsForm.value" :invalid="!!statsErrors.value" />
        </FormField>
        <FormField label="Suffix" hint="e.g. + or %" :error="statsErrors.suffix">
          <BaseInput v-model="statsForm.suffix" :invalid="!!statsErrors.suffix" />
        </FormField>
        <FormField label="Label" :error="statsErrors.label">
          <BaseInput v-model="statsForm.label" :invalid="!!statsErrors.label" placeholder="Pieces this season" />
        </FormField>
        <FormField label="Sort order" :error="statsErrors.sort_order">
          <BaseInput v-model="statsForm.sort_order" type="number" :invalid="!!statsErrors.sort_order" />
        </FormField>
        <div class="col-span-2">
          <FormField label="Published">
            <BaseToggle v-model="statsForm.is_active" label="Show on the home page" />
          </FormField>
        </div>
      </div>
      <template #foot>
        <BaseButton variant="ghost" @click="statsModal = false">Cancel</BaseButton>
        <BaseButton variant="gold" :loading="statsSaving" @click="saveStat">
          {{ statsForm._id == null ? 'Create' : 'Save changes' }}
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Marquee modal -->
    <BaseModal v-model="marqueeModal" :title="marqueeForm._id == null ? 'New marquee word' : 'Edit marquee word'">
      <div class="form-grid">
        <FormField label="Word" required :error="marqueeErrors.word">
          <BaseInput v-model="marqueeForm.word" :invalid="!!marqueeErrors.word" />
        </FormField>
        <FormField label="Sort order" :error="marqueeErrors.sort_order">
          <BaseInput v-model="marqueeForm.sort_order" type="number" :invalid="!!marqueeErrors.sort_order" />
        </FormField>
        <div class="col-span-2">
          <FormField label="Published">
            <BaseToggle v-model="marqueeForm.is_active" label="Show on the home page" />
          </FormField>
        </div>
      </div>
      <template #foot>
        <BaseButton variant="ghost" @click="marqueeModal = false">Cancel</BaseButton>
        <BaseButton variant="gold" :loading="marqueeSaving" @click="saveMarquee">
          {{ marqueeForm._id == null ? 'Create' : 'Save changes' }}
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Delete confirmations -->
    <ConfirmDialog v-model="featuredDelete" title="Delete featured card"
      :message="`Delete “${featuredDelete?.title}”?`" :loading="featuredDeleting" @confirm="confirmFeaturedDelete" />
    <ConfirmDialog v-model="statsDelete" title="Delete stat"
      :message="`Delete the “${statsDelete?.label}” stat?`" :loading="statsDeleting" @confirm="confirmStatDelete" />
    <ConfirmDialog v-model="marqueeDelete" title="Delete marquee word"
      :message="`Delete “${marqueeDelete?.word}”?`" :loading="marqueeDeleting" @confirm="confirmMarqueeDelete" />
  </div>
</template>

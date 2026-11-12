<script setup>
import { onMounted, reactive, ref } from 'vue'
import { adminApi } from '@/api/admin'
import { useToast } from '@/composables/useToast'
import { ApiError } from '@/api/client'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseToggle from '@/components/ui/BaseToggle.vue'
import FormField from '@/components/ui/FormField.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const toast = useToast()

const items = ref([])
const loading = ref(true)
const error = ref('')

const modalOpen = ref(false)
const editingId = ref(null)
const saving = ref(false)
const form = reactive(blankForm())
const formErrors = ref({})

const deleteTarget = ref(null)
const deleting = ref(false)

function blankForm() {
  return {
    name: '',
    season: '',
    pieces: 0,
    monogram: '',
    image_url: '',
    sort_order: 0,
    is_active: true,
  }
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await adminApi.listCollections()
    items.value = res.collections ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load collections.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  Object.assign(form, blankForm())
  editingId.value = null
  formErrors.value = {}
  modalOpen.value = true
}

function openEdit(item) {
  editingId.value = item.id
  formErrors.value = {}
  Object.assign(form, {
    name: item.name ?? '',
    season: item.season ?? '',
    pieces: item.pieces ?? 0,
    monogram: item.monogram ?? '',
    image_url: item.image_url ?? '',
    sort_order: item.sort_order ?? 0,
    is_active: !!item.is_active,
  })
  modalOpen.value = true
}

async function save() {
  saving.value = true
  try {
    if (editingId.value === null) {
      await adminApi.createCollection({ ...form })
      toast.success('Collection created')
    } else {
      await adminApi.updateCollection(editingId.value, { ...form })
      toast.success('Collection updated')
    }
    modalOpen.value = false
    await load()
  } catch (e) {
    if (e instanceof ApiError && e.errors) formErrors.value = e.errors
    else toast.error('Save failed', e.message)
  } finally {
    saving.value = false
  }
}

function askDelete(item) {
  deleteTarget.value = item
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await adminApi.deleteCollection(deleteTarget.value.id)
    toast.success('Collection deleted')
    deleteTarget.value = null
    await load()
  } catch (e) {
    toast.error('Delete failed', e.message)
  } finally {
    deleting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-head row row--between">
      <div>
        <h1 class="page-head__title">Collections</h1>
        <p class="page-head__desc">Curated categories shown on the /collection page.</p>
      </div>
      <BaseButton variant="gold" @click="openCreate">+ New collection</BaseButton>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-block"><LoadingSpinner dark /> Loading…</div>
      <p v-else-if="error" class="text-danger" style="padding: 20px">{{ error }}</p>
      <EmptyState
        v-else-if="items.length === 0"
        title="No collections yet"
        message="Create your first collection to show it on the guest site."
      >
        <BaseButton variant="gold" @click="openCreate">+ New collection</BaseButton>
      </EmptyState>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Season</th>
              <th>Pieces</th>
              <th>Order</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>
                <div class="table__cell-title">{{ item.name }}</div>
                <div v-if="item.monogram" class="table__cell-sub">{{ item.monogram }}</div>
              </td>
              <td>{{ item.season || '—' }}</td>
              <td>{{ item.pieces }}</td>
              <td>{{ item.sort_order }}</td>
              <td>
                <span class="badge" :class="item.is_active ? 'badge--on' : 'badge--off'">
                  <span class="badge__dot"></span>{{ item.is_active ? 'Published' : 'Draft' }}
                </span>
              </td>
              <td class="table__actions">
                <BaseButton variant="ghost" size="sm" @click="openEdit(item)">Edit</BaseButton>
                <BaseButton variant="danger" size="sm" @click="askDelete(item)">Delete</BaseButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingId === null ? 'New collection' : 'Edit collection'">
      <form @submit.prevent="save">
        <div class="form-grid">
          <FormField label="Name" required :error="formErrors.name">
            <BaseInput v-model="form.name" :invalid="!!formErrors.name" placeholder="e.g. Tailored Essentials" />
          </FormField>
          <FormField label="Season" :error="formErrors.season">
            <BaseInput v-model="form.season" placeholder="Autumn / Winter 2025" />
          </FormField>
          <FormField label="Pieces" hint="Number of pieces in this collection" :error="formErrors.pieces">
            <BaseInput v-model="form.pieces" type="number" :invalid="!!formErrors.pieces" />
          </FormField>
          <FormField label="Monogram" hint="Optional 1–8 character label" :error="formErrors.monogram">
            <BaseInput v-model="form.monogram" :invalid="!!formErrors.monogram" />
          </FormField>
          <FormField label="Image URL" :error="formErrors.image_url">
            <BaseInput v-model="form.image_url" :invalid="!!formErrors.image_url" placeholder="https://…" />
          </FormField>
          <FormField label="Sort order" :error="formErrors.sort_order">
            <BaseInput v-model="form.sort_order" type="number" :invalid="!!formErrors.sort_order" />
          </FormField>
          <div class="col-span-2">
            <FormField label="Published">
              <BaseToggle v-model="form.is_active" label="Show on the guest site" />
            </FormField>
          </div>
        </div>
      </form>
      <template #foot>
        <BaseButton variant="ghost" @click="modalOpen = false">Cancel</BaseButton>
        <BaseButton variant="gold" :loading="saving" @click="save">
          {{ editingId === null ? 'Create' : 'Save changes' }}
        </BaseButton>
      </template>
    </BaseModal>

    <ConfirmDialog
      v-model="deleteTarget"
      title="Delete collection"
      :message="`Delete “${deleteTarget?.name}”? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </div>
</template>

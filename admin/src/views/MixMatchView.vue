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
    label: '',
    title: '',
    description: '',
    pieces: '',
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
    const res = await adminApi.listLooks()
    items.value = res.looks ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load looks.'
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
    label: item.label ?? '',
    title: item.title ?? '',
    description: item.description ?? '',
    pieces: item.pieces ?? '',
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
      await adminApi.createLook({ ...form })
      toast.success('Look created')
    } else {
      await adminApi.updateLook(editingId.value, { ...form })
      toast.success('Look updated')
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
    await adminApi.deleteLook(deleteTarget.value.id)
    toast.success('Look deleted')
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
        <h1 class="page-head__title">Mix &amp; Match</h1>
        <p class="page-head__desc">Curated outfits shown on the /mix-match page.</p>
      </div>
      <BaseButton variant="gold" @click="openCreate">+ New look</BaseButton>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-block"><LoadingSpinner dark /> Loading…</div>
      <p v-else-if="error" class="text-danger" style="padding: 20px">{{ error }}</p>
      <EmptyState
        v-else-if="items.length === 0"
        title="No looks yet"
        message="Create a curated outfit to feature on the guest site."
      >
        <BaseButton variant="gold" @click="openCreate">+ New look</BaseButton>
      </EmptyState>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Label</th>
              <th>Pieces</th>
              <th>Order</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>
                <div class="table__cell-title">{{ item.title }}</div>
                <div class="table__cell-sub truncate" style="max-width: 320px">{{ item.description }}</div>
              </td>
              <td>{{ item.label || '—' }}</td>
              <td class="nowrap text-muted">{{ item.pieces || '—' }}</td>
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

    <BaseModal v-model="modalOpen" :title="editingId === null ? 'New look' : 'Edit look'">
      <form @submit.prevent="save">
        <div class="form-grid">
          <FormField label="Title" required :error="formErrors.title">
            <BaseInput v-model="form.title" :invalid="!!formErrors.title" placeholder="The Relaxed Tailor" />
          </FormField>
          <FormField label="Label" hint="Short eyebrow text" :error="formErrors.label">
            <BaseInput v-model="form.label" :invalid="!!formErrors.label" placeholder="Look 01" />
          </FormField>
          <div class="col-span-2">
            <FormField label="Description" :error="formErrors.description">
              <BaseTextarea v-model="form.description" :invalid="!!formErrors.description" rows="4"
                placeholder="Describe the combination and how to wear it." />
            </FormField>
          </div>
          <FormField label="Pieces" hint="Comma-separated list, e.g. Wool blazer, silk shirt" :error="formErrors.pieces">
            <BaseInput v-model="form.pieces" :invalid="!!formErrors.pieces" />
          </FormField>
          <FormField label="Monogram" :error="formErrors.monogram">
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
      title="Delete look"
      :message="`Delete “${deleteTarget?.title}”? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </div>
</template>

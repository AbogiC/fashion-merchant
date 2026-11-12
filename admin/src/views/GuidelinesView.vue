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
  return { number: '', title: '', description: '', sort_order: 0, is_active: true }
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await adminApi.listGuidelines()
    items.value = res.guidelines ?? []
  } catch (e) {
    error.value = e.message || 'Unable to load guidelines.'
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
    number: item.number ?? '',
    title: item.title ?? '',
    description: item.description ?? '',
    sort_order: item.sort_order ?? 0,
    is_active: !!item.is_active,
  })
  modalOpen.value = true
}

async function save() {
  saving.value = true
  try {
    if (editingId.value === null) {
      await adminApi.createGuideline({ ...form })
      toast.success('Guideline created')
    } else {
      await adminApi.updateGuideline(editingId.value, { ...form })
      toast.success('Guideline updated')
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
    await adminApi.deleteGuideline(deleteTarget.value.id)
    toast.success('Guideline deleted')
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
        <h1 class="page-head__title">Guidelines</h1>
        <p class="page-head__desc">Style principles shown on the /guidelines page.</p>
      </div>
      <BaseButton variant="gold" @click="openCreate">+ New guideline</BaseButton>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-block"><LoadingSpinner dark /> Loading…</div>
      <p v-else-if="error" class="text-danger" style="padding: 20px">{{ error }}</p>
      <EmptyState
        v-else-if="items.length === 0"
        title="No guidelines yet"
        message="Add the style principles your brand lives by."
      >
        <BaseButton variant="gold" @click="openCreate">+ New guideline</BaseButton>
      </EmptyState>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Description</th>
              <th>Order</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td class="nowrap text-muted">{{ item.number || '—' }}</td>
              <td>
                <div class="table__cell-title">{{ item.title }}</div>
              </td>
              <td>
                <div class="table__cell-sub truncate" style="max-width: 380px">{{ item.description }}</div>
              </td>
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

    <BaseModal v-model="modalOpen" :title="editingId === null ? 'New guideline' : 'Edit guideline'">
      <form @submit.prevent="save">
        <div class="form-grid">
          <FormField label="Number / label" hint="e.g. 01 or I" :error="formErrors.number">
            <BaseInput v-model="form.number" :invalid="!!formErrors.number" />
          </FormField>
          <FormField label="Sort order" :error="formErrors.sort_order">
            <BaseInput v-model="form.sort_order" type="number" :invalid="!!formErrors.sort_order" />
          </FormField>
          <div class="col-span-2">
            <FormField label="Title" required :error="formErrors.title">
              <BaseInput v-model="form.title" :invalid="!!formErrors.title" placeholder="Fit is everything" />
            </FormField>
          </div>
          <div class="col-span-2">
            <FormField label="Description" :error="formErrors.description">
              <BaseTextarea v-model="form.description" :invalid="!!formErrors.description" rows="5"
                placeholder="Explain this principle in a sentence or two." />
            </FormField>
          </div>
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
      title="Delete guideline"
      :message="`Delete “${deleteTarget?.title}”? This cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </div>
</template>

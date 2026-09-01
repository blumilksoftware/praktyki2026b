<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPencil, IconTrash } from '@tabler/icons-vue'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  industryTags: { type: Array, default: () => [] },
})

const { t } = useI18n()

const createForm = useForm({ name: '' })
const editForm = useForm({ name: '' })
const editingId = ref(null)
const deleteTarget = ref(null)

function createTag() {
  createForm.post(ROUTES.ADMIN_INDUSTRY_TAGS, {
    preserveScroll: true,
    onSuccess: () => createForm.reset(),
  })
}

function startEdit(tag) {
  editForm.clearErrors()
  editForm.name = tag.name
  editingId.value = tag.id
}

function cancelEdit() {
  editingId.value = null
}

function saveEdit(tag) {
  editForm.patch(ROUTES.ADMIN_INDUSTRY_TAG(tag.id), {
    preserveScroll: true,
    onSuccess: () => {
      editingId.value = null
    },
  })
}

function openDelete(tag) {
  deleteTarget.value = tag
}

function closeDelete() {
  deleteTarget.value = null
}

function confirmDelete() {
  useForm({}).delete(ROUTES.ADMIN_INDUSTRY_TAG(deleteTarget.value.id), {
    preserveScroll: true,
    onSuccess: closeDelete,
  })
}
</script>

<template>
  <Head :title="t('admin.industryTags.title')" />
  <AdminLayout active-page="industryTags">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.industryTags.title') }}</h1>
        <p class="mt-2 text-slate-600 text-sm">{{ t('admin.industryTags.description') }}</p>
      </div>

      <ProfilePageCard>
        <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="createTag">
          <div class="flex-1">
            <BaseInput
              id="new-industry-tag-name"
              v-model="createForm.name"
              :label="t('admin.industryTags.addLabel')"
              :error="createForm.errors.name"
            />
          </div>
          <BaseButton type="submit" class="min-w-full sm:min-w-44" :disabled="createForm.processing">
            {{ t('admin.industryTags.addButton') }}
          </BaseButton>
        </form>

        <div v-if="industryTags.length > 0" class="mt-6 flex flex-col divide-y divide-border">
          <div v-for="tag in industryTags" :key="tag.id" class="flex items-center justify-between gap-3 py-3">
            <template v-if="editingId === tag.id">
              <div class="flex-1">
                <BaseInput
                  :id="`edit-industry-tag-${tag.id}`"
                  v-model="editForm.name"
                  :label="t('admin.industryTags.editLabel')"
                  :error="editForm.errors.name"
                />
              </div>
              <div class="flex gap-2">
                <BaseButton type="button" variant="secondary" :disabled="editForm.processing" @click="cancelEdit">
                  {{ t('admin.industryTags.cancel') }}
                </BaseButton>
                <BaseButton type="button" :disabled="editForm.processing" @click="saveEdit(tag)">
                  {{ t('admin.industryTags.save') }}
                </BaseButton>
              </div>
            </template>
            <template v-else>
              <span class="text-text">{{ tag.name }}</span>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="cursor-pointer rounded-lg p-2 text-additional transition hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                  :aria-label="t('admin.industryTags.editAriaLabel', { name: tag.name })"
                  @click="startEdit(tag)"
                >
                  <IconPencil class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                  type="button"
                  class="cursor-pointer rounded-lg p-2 text-error transition hover:bg-error/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40"
                  :aria-label="t('admin.industryTags.deleteAriaLabel', { name: tag.name })"
                  @click="openDelete(tag)"
                >
                  <IconTrash class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </template>
          </div>
        </div>

        <p v-else class="mt-6 text-additional italic">
          {{ t('admin.industryTags.empty') }}
        </p>
      </ProfilePageCard>
    </div>

    <BaseModal
      :open="deleteTarget !== null"
      :title="t('admin.industryTags.deleteTitle')"
      max-width-class="max-w-lg"
      @close="closeDelete"
    >
      <div v-if="deleteTarget" class="flex flex-col gap-4">
        <p class="text-text">
          {{ t('admin.industryTags.deleteConfirmation', { name: deleteTarget.name }) }}
        </p>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <BaseButton type="button" variant="secondary" class="min-w-32" @click="closeDelete">
            {{ t('admin.industryTags.cancel') }}
          </BaseButton>
          <BaseButton type="button" class="min-w-32" @click="confirmDelete">
            {{ t('admin.industryTags.confirmDelete') }}
          </BaseButton>
        </div>
      </div>
    </BaseModal>
  </AdminLayout>
</template>

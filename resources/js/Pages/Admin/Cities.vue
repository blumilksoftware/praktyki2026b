<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  cities: { type: Array, default: () => [] },
})

const { t } = useI18n()

const createForm = useForm({ name: '' })
const editForm = useForm({ name: '' })
const editingId = ref(null)
const deleteTarget = ref(null)

function createCity() {
  createForm.post(ROUTES.ADMIN_CITIES, {
    preserveScroll: true,
    onSuccess: () => createForm.reset(),
  })
}

function startEdit(city) {
  editForm.clearErrors()
  editForm.name = city.name
  editingId.value = city.id
}

function cancelEdit() {
  editingId.value = null
}

function saveEdit(city) {
  editForm.patch(ROUTES.ADMIN_CITY(city.id), {
    preserveScroll: true,
    onSuccess: () => {
      editingId.value = null
    },
  })
}

function openDelete(city) {
  deleteTarget.value = city
}

function closeDelete() {
  deleteTarget.value = null
}

function confirmDelete() {
  useForm({}).delete(ROUTES.ADMIN_CITY(deleteTarget.value.id), {
    preserveScroll: true,
    onSuccess: closeDelete,
  })
}
</script>

<template>
  <Head :title="t('admin.cities.title')" />
  <AdminLayout active-page="cities">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.cities.title') }}</h1>
        <p class="mt-2 text-slate-600 text-sm">{{ t('admin.cities.description') }}</p>
      </div>

      <ProfilePageCard>
        <form class="flex flex-col gap-3 sm:flex-row sm:items-start" @submit.prevent="createCity">
          <div class="flex-1">
            <BaseInput
              id="new-city-name"
              v-model="createForm.name"
              :label="t('admin.cities.addLabel')"
              :error="createForm.errors.name"
            />
          </div>
          <BaseButton type="submit" class="min-w-full sm:min-w-44 sm:mt-6" :disabled="createForm.processing">
            {{ t('admin.cities.addButton') }}
          </BaseButton>
        </form>

        <div v-if="cities.length > 0" class="mt-6 flex flex-col divide-y divide-border">
          <div v-for="city in cities" :key="city.id" class="flex items-center justify-between gap-3 py-3">
            <template v-if="editingId === city.id">
              <div class="flex-1">
                <BaseInput
                  :id="`edit-city-${city.id}`"
                  v-model="editForm.name"
                  :label="t('admin.cities.addLabel')"
                  :error="editForm.errors.name"
                />
              </div>
              <div class="flex gap-2">
                <BaseButton type="button" variant="secondary" :disabled="editForm.processing" @click="cancelEdit">
                  {{ t('admin.cities.cancel') }}
                </BaseButton>
                <BaseButton type="button" :disabled="editForm.processing" @click="saveEdit(city)">
                  {{ t('admin.cities.save') }}
                </BaseButton>
              </div>
            </template>
            <template v-else>
              <span class="text-text">{{ city.name }}</span>
              <div class="flex gap-2">
                <BaseButton type="button" variant="secondary" @click="startEdit(city)">
                  {{ t('admin.cities.edit') }}
                </BaseButton>
                <BaseButton type="button" variant="secondary" @click="openDelete(city)">
                  {{ t('admin.cities.delete') }}
                </BaseButton>
              </div>
            </template>
          </div>
        </div>

        <p v-else class="mt-6 text-additional italic">
          {{ t('admin.cities.empty') }}
        </p>
      </ProfilePageCard>
    </div>

    <BaseModal
      :open="deleteTarget !== null"
      :title="t('admin.cities.deleteTitle')"
      max-width-class="max-w-lg"
      @close="closeDelete"
    >
      <div v-if="deleteTarget" class="flex flex-col gap-4">
        <p class="text-text">
          {{ t('admin.cities.deleteConfirmation', { name: deleteTarget.name }) }}
        </p>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <BaseButton type="button" variant="secondary" class="min-w-32" @click="closeDelete">
            {{ t('admin.cities.cancel') }}
          </BaseButton>
          <BaseButton type="button" class="min-w-32" @click="confirmDelete">
            {{ t('admin.cities.confirmDelete') }}
          </BaseButton>
        </div>
      </div>
    </BaseModal>
  </AdminLayout>
</template>

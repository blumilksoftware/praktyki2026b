<script setup>
import { computed, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import FacultyCard from '@/Components/University/FacultyCard.vue'
import { ROUTES } from '@/Helpers/routes'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

const props = defineProps({
  faculties: { type: Array, default: () => [] },
})

const { t } = useI18n()

const createForm = useForm({ name: '' })
const deleteForm = useForm({ reassign_to: '' })
const deleteTarget = ref(null)

const isFacultyTarget = computed(() => deleteTarget.value?.type === 'faculty')

const targetUsage = computed(() => {
  if (deleteTarget.value === null) {
    return { students: 0, offers: 0 }
  }

  const fields = isFacultyTarget.value ? deleteTarget.value.item.study_fields : [deleteTarget.value.item]

  return fields.reduce((usage, field) => ({
    students: usage.students + field.students_count,
    offers: usage.offers + field.offers_count,
  }), { students: 0, offers: 0 })
})

const requiresReassign = computed(() => targetUsage.value.students > 0 || targetUsage.value.offers > 0)

const reassignOptions = computed(() => {
  if (deleteTarget.value === null) {
    return []
  }

  if (isFacultyTarget.value) {
    return props.faculties
      .filter((faculty) => faculty.id !== deleteTarget.value.item.id)
      .map((faculty) => ({ value: faculty.id, label: faculty.name }))
  }

  const faculty = props.faculties.find((item) => item.id === deleteTarget.value.facultyId)

  return (faculty?.study_fields ?? [])
    .filter((field) => field.id !== deleteTarget.value.item.id)
    .map((field) => ({ value: field.id, label: field.name }))
})

function createFaculty() {
  createForm.post(ROUTES.UNIVERSITY_FACULTIES, {
    preserveScroll: true,
    onSuccess: () => createForm.reset(),
  })
}

function openFacultyDelete(faculty) {
  deleteForm.clearErrors()
  deleteForm.reassign_to = ''
  deleteTarget.value = { type: 'faculty', item: faculty }
}

function openFieldDelete({ field, faculty }) {
  deleteForm.clearErrors()
  deleteForm.reassign_to = ''
  deleteTarget.value = { type: 'field', item: field, facultyId: faculty.id }
}

function closeDelete() {
  deleteTarget.value = null
}

function confirmDelete() {
  const url = isFacultyTarget.value
    ? ROUTES.UNIVERSITY_FACULTY(deleteTarget.value.item.id)
    : ROUTES.UNIVERSITY_STUDY_FIELD(deleteTarget.value.item.id)

  deleteForm.delete(url, {
    preserveScroll: true,
    onSuccess: closeDelete,
  })
}
</script>

<template>
  <Head :title="t('university.faculties.title')" />

  <AppLayout active-page="faculties">
    <h1 class="font-semibold text-2xl text-text">{{ t('university.faculties.title') }}</h1>
    <p class="mt-2 text-additional">{{ t('university.faculties.subtitle') }}</p>

    <form
      class="mt-6 flex flex-col gap-3 rounded-2xl border border-border bg-white p-4 shadow-sm sm:flex-row sm:items-start sm:p-5"
      @submit.prevent="createFaculty"
    >
      <div class="flex-1">
        <BaseInput
          id="new-faculty-name"
          v-model="createForm.name"
          :label="t('university.faculties.addFacultyLabel')"
          :error="createForm.errors.name"
        />
      </div>
      <div class="flex flex-col gap-1.5">
        <span aria-hidden="true" class="mb-1 hidden text-sm font-medium sm:block">&nbsp;</span>
        <BaseButton type="submit" class="min-w-full border border-transparent sm:min-w-44 sm:text-base" :disabled="createForm.processing">
          {{ t('university.faculties.addFaculty') }}
        </BaseButton>
      </div>
    </form>

    <div v-if="faculties.length > 0" class="mt-6 flex flex-col gap-4">
      <FacultyCard
        v-for="faculty in faculties"
        :key="faculty.id"
        :faculty="faculty"
        @delete-faculty="openFacultyDelete"
        @delete-field="openFieldDelete"
      />
    </div>

    <p v-else class="mt-6 text-additional italic">
      {{ t('university.faculties.empty') }}
    </p>

    <BaseModal
      :open="deleteTarget !== null"
      :title="isFacultyTarget ? t('university.faculties.deleteFacultyTitle') : t('university.faculties.deleteFieldTitle')"
      max-width-class="max-w-lg"
      @close="closeDelete"
    >
      <form v-if="deleteTarget" class="flex flex-col gap-4" novalidate @submit.prevent="confirmDelete">
        <p class="text-text">
          {{ t('university.faculties.deleteConfirmation', { name: deleteTarget.item.name }) }}
        </p>

        <p v-if="requiresReassign" class="text-additional text-sm">
          {{ t('university.faculties.usage', { students: targetUsage.students, offers: targetUsage.offers }) }}
        </p>

        <template v-if="requiresReassign">
          <BaseSelect
            v-if="reassignOptions.length > 0"
            id="reassign-target"
            v-model="deleteForm.reassign_to"
            :label="isFacultyTarget ? t('university.faculties.reassignFacultyLabel') : t('university.faculties.reassignFieldLabel')"
            :options="reassignOptions"
            :placeholder="t('university.faculties.reassignPlaceholder')"
            :error="deleteForm.errors.reassign_to"
            required
          />
          <p v-else class="text-error text-sm">
            {{ t('university.faculties.reassignUnavailable') }}
          </p>
        </template>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <BaseButton type="button" variant="secondary" class="min-w-32" @click="closeDelete">
            {{ t('university.faculties.cancel') }}
          </BaseButton>
          <BaseButton
            type="submit"
            class="min-w-32 border border-transparent"
            :disabled="deleteForm.processing || (requiresReassign && reassignOptions.length === 0)"
          >
            {{ t('university.faculties.confirmDelete') }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </AppLayout>
</template>

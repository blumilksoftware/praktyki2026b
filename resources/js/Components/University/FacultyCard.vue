<script setup>
import { nextTick, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPencil, IconTrash } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  faculty: { type: Object, required: true },
})

const emit = defineEmits(['deleteFaculty', 'deleteField'])

const { t } = useI18n()

const isRenaming = ref(false)
const editingFieldId = ref(null)

const renameInput = ref(null)
const renameTrigger = ref(null)
const fieldRenameInput = ref(null)
const fieldTriggers = ref({})

const renameForm = useForm({ name: props.faculty.name })
const createFieldForm = useForm({ name: '' })
const renameFieldForm = useForm({ name: '' })

function setFieldTrigger(fieldId, element) {
  fieldTriggers.value[fieldId] = element
}

function setFieldRenameInput(element) {
  fieldRenameInput.value = element
}

function startRename() {
  renameForm.clearErrors()
  renameForm.name = props.faculty.name
  isRenaming.value = true
  nextTick(() => renameInput.value?.focus())
}

function closeRename() {
  isRenaming.value = false
  nextTick(() => renameTrigger.value?.focus())
}

function submitRename() {
  renameForm.patch(ROUTES.UNIVERSITY_FACULTY(props.faculty.id), {
    preserveScroll: true,
    onSuccess: closeRename,
  })
}

function createField() {
  createFieldForm.post(ROUTES.UNIVERSITY_FACULTY_STUDY_FIELDS(props.faculty.id), {
    preserveScroll: true,
    onSuccess: () => createFieldForm.reset(),
  })
}

function startFieldRename(field) {
  renameFieldForm.clearErrors()
  renameFieldForm.name = field.name
  editingFieldId.value = field.id
  nextTick(() => fieldRenameInput.value?.focus())
}

function closeFieldRename(field) {
  editingFieldId.value = null
  nextTick(() => fieldTriggers.value[field.id]?.focus())
}

function submitFieldRename(field) {
  renameFieldForm.patch(ROUTES.UNIVERSITY_STUDY_FIELD(field.id), {
    preserveScroll: true,
    onSuccess: () => closeFieldRename(field),
  })
}
</script>

<template>
  <div class="rounded-2xl border border-border bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <form v-if="isRenaming" class="flex w-full flex-col gap-3" @submit.prevent="submitRename">
        <BaseInput
          :id="`faculty-name-${faculty.id}`"
          ref="renameInput"
          v-model="renameForm.name"
          :label="t('university.faculties.facultyName')"
          :error="renameForm.errors.name"
          required
        />
        <div class="flex flex-wrap justify-end gap-2">
          <BaseButton type="button" variant="secondary" class="min-w-32" @click="closeRename">
            {{ t('university.faculties.cancel') }}
          </BaseButton>
          <BaseButton type="submit" class="min-w-32 border border-transparent" :disabled="renameForm.processing">
            {{ t('university.faculties.save') }}
          </BaseButton>
        </div>
      </form>

      <template v-else>
        <h2 class="font-semibold text-text text-lg">{{ faculty.name }}</h2>

        <div class="flex items-center gap-2">
          <button
            ref="renameTrigger"
            type="button"
            class="rounded-lg p-2 text-additional transition hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :aria-label="t('university.faculties.renameFaculty', { name: faculty.name })"
            @click="startRename"
          >
            <IconPencil class="h-5 w-5" aria-hidden="true" />
          </button>
          <button
            type="button"
            class="rounded-lg p-2 text-error transition hover:bg-error/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40"
            :aria-label="t('university.faculties.deleteFaculty', { name: faculty.name })"
            @click="emit('deleteFaculty', faculty)"
          >
            <IconTrash class="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
      </template>
    </div>

    <ul v-if="faculty.study_fields.length > 0" class="mt-4 flex flex-col gap-2">
      <li
        v-for="field in faculty.study_fields"
        :key="field.id"
        class="rounded-lg border border-border bg-white px-3 py-2"
      >
        <form
          v-if="editingFieldId === field.id"
          class="flex flex-col gap-3"
          @submit.prevent="submitFieldRename(field)"
        >
          <BaseInput
            :id="`study-field-name-${field.id}`"
            :ref="setFieldRenameInput"
            v-model="renameFieldForm.name"
            :label="t('university.faculties.fieldName')"
            :error="renameFieldForm.errors.name"
            required
          />
          <div class="flex flex-wrap justify-end gap-2">
            <BaseButton type="button" variant="secondary" class="min-w-32" @click="closeFieldRename(field)">
              {{ t('university.faculties.cancel') }}
            </BaseButton>
            <BaseButton type="submit" class="min-w-32 border border-transparent" :disabled="renameFieldForm.processing">
              {{ t('university.faculties.save') }}
            </BaseButton>
          </div>
        </form>

        <div v-else class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <p class="font-medium text-text">{{ field.name }}</p>
            <p class="text-additional text-sm">
              {{ t('university.faculties.usage', { students: field.students_count, offers: field.offers_count }) }}
            </p>
          </div>

          <div class="-mr-3 flex items-center gap-2">
            <button
              :ref="(element) => setFieldTrigger(field.id, element)"
              type="button"
              class="rounded-lg p-2 text-additional transition hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
              :aria-label="t('university.faculties.renameField', { name: field.name })"
              @click="startFieldRename(field)"
            >
              <IconPencil class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
              type="button"
              class="rounded-lg p-2 text-error transition hover:bg-error/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40"
              :aria-label="t('university.faculties.deleteField', { name: field.name })"
              @click="emit('deleteField', { field, faculty })"
            >
              <IconTrash class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </li>
    </ul>

    <p v-else class="mt-4 text-additional text-sm italic">
      {{ t('university.faculties.noFields') }}
    </p>

    <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="createField">
      <div class="flex-1">
        <BaseInput
          :id="`new-study-field-${faculty.id}`"
          v-model="createFieldForm.name"
          :label="t('university.faculties.addFieldLabel')"
          :placeholder="t('university.faculties.addFieldPlaceholder')"
          :error="createFieldForm.errors.name"
        />
      </div>
      <BaseButton type="submit" variant="secondary" class="min-w-full sm:min-w-44 sm:text-base" :disabled="createFieldForm.processing">
        {{ t('university.faculties.addField') }}
      </BaseButton>
    </form>
  </div>
</template>

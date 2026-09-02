<script setup>
import { nextTick, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconChevronDown, IconPencil, IconTrash } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  faculty: { type: Object, required: true },
})

const emit = defineEmits(['deleteFaculty', 'deleteField'])

const { t } = useI18n()

const isExpanded = ref(false)
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
    <div class="flex flex-row flex-wrap items-center justify-between gap-3">
      <form v-if="isRenaming" class="flex w-full flex-col gap-3" novalidate @submit.prevent="submitRename">
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
        <h2 class="min-w-0 flex-1">
          <button
            type="button"
            class="flex w-full min-w-0 items-center gap-2 rounded-lg text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :aria-expanded="isExpanded"
            :aria-controls="`faculty-panel-${faculty.id}`"
            @click="isExpanded = !isExpanded"
          >
            <IconChevronDown
              class="h-5 w-5 shrink-0 text-additional transition-transform"
              :class="{ 'rotate-180': isExpanded }"
              aria-hidden="true"
            />
            <span class="min-w-0 truncate font-semibold text-text text-lg">{{ faculty.name }}</span>
            <span class="shrink-0 text-additional text-sm">
              {{ t('university.faculties.fieldsCount', { count: faculty.study_fields.length }) }}
            </span>
          </button>
        </h2>

        <div class="flex items-center gap-2 shrink-0 sm:mr-1.5">
          <button
            ref="renameTrigger"
            type="button"
            class="inline-flex w-fit shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg border border-border bg-white/60 px-2 py-1.5 text-sm font-medium text-text transition hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 cursor-pointer"
            :aria-label="t('university.faculties.renameFaculty', { name: faculty.name })"
            @click="startRename"
          >
            <IconPencil class="h-4 w-4 shrink-0" aria-hidden="true" />
            <span class="hidden sm:inline">{{ t('university.faculties.edit') }}</span>
          </button>
          <button
            type="button"
            class="inline-flex w-fit shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            :aria-label="t('university.faculties.confirmDelete', { name: faculty.name })"
            @click="emit('deleteFaculty', faculty)"
          >
            <IconTrash class="h-4 w-4 shrink-0" aria-hidden="true" />
            <span class="hidden sm:inline">{{ t('university.faculties.confirmDelete') }}</span>
          </button>
        </div>
      </template>
    </div>

    <div
      :id="`faculty-panel-${faculty.id}`"
      class="grid transition-[grid-template-rows] duration-300 ease-out"
      :style="{ gridTemplateRows: isExpanded ? '1fr' : '0fr' }"
      :inert="!isExpanded"
    >
      <div class="-mx-1 min-h-0 overflow-hidden px-1 pb-1">
        <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start" @submit.prevent="createField">
          <div class="flex-1">
            <BaseInput
              :id="`new-study-field-${faculty.id}`"
              v-model="createFieldForm.name"
              :label="t('university.faculties.addFieldLabel')"
              :error="createFieldForm.errors.name"
            />
          </div>
          <div class="flex flex-col gap-1.5">
            <span aria-hidden="true" class="mb-1 hidden text-sm font-medium sm:block">&nbsp;</span>
            <BaseButton type="submit" variant="secondary" class="min-w-full sm:min-w-44 sm:text-base" :disabled="createFieldForm.processing">
              {{ t('university.faculties.addField') }}
            </BaseButton>
          </div>
        </form>

        <ul v-if="faculty.study_fields.length > 0" class="mt-4 flex flex-col gap-2">
          <li
            v-for="field in faculty.study_fields"
            :key="field.id"
            class="rounded-lg border border-border bg-white py-2 pl-3 pr-3"
          >
            <form
              v-if="editingFieldId === field.id"
              class="flex flex-col gap-3"
              novalidate
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

            <div v-else class="flex flex-row items-center justify-between gap-2">
              <div class="min-w-0">
                <p class="truncate font-medium text-text">{{ field.name }}</p>
                <p class="text-additional text-sm">
                  {{ t('university.faculties.usage', { students: field.students_count, offers: field.offers_count }) }}
                </p>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <button
                  :ref="(element) => setFieldTrigger(field.id, element)"
                  type="button"
                  class="inline-flex w-fit shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg border border-border bg-white/60 px-2 py-1.5 text-sm font-medium text-text transition hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 cursor-pointer"
                  :aria-label="t('university.faculties.edit', { name: field.name })"
                  @click="startFieldRename(field)"
                >
                  <IconPencil class="h-4 w-4 shrink-0" aria-hidden="true" />
                  <span class="hidden sm:inline">{{ t('university.faculties.edit') }}</span>
                </button>
                <button
                  type="button"
                  class="inline-flex w-fit shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                  :aria-label="t('university.faculties.confirmDelete', { name: field.name })"
                  @click="emit('deleteField', { field, faculty })"
                >
                  <IconTrash class="h-4 w-4 shrink-0" aria-hidden="true" />
                  <span class="hidden sm:inline">{{ t('university.faculties.confirmDelete') }}</span>
                </button>
              </div>
            </div>
          </li>
        </ul>

        <p v-else class="mt-4 text-additional text-sm italic">
          {{ t('university.faculties.noFields') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import ProfileTagInput from '@/Components/Profile/ProfileTagInput.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  open: { type: Boolean, required: true },
  user: { type: Object, required: true },
  studyFields: { type: Array, default: () => [] },
  focusSection: { type: String, default: null },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const fileInput = ref(null)
const isDragging = ref(false)
const pendingFile = ref(null)
const previewUrl = ref(null)
const localError = ref(null)
const MAX_SIZE_BYTES = 2 * 1024 * 1024
const universityOptions = [
  'Collegium Witelona',
  'Politechnika Wrocławska',
  'Uniwersytet Wrocławski',
  'Uniwersytet Ekonomiczny we Wrocławiu',
  'Akademia Nauk Stosowanych Angelusa Silesiusa',
]

const photoForm = useForm({ photo: null })

const profileForm = useForm({
  first_name: props.user.first_name ?? '',
  last_name: props.user.last_name ?? '',
  age: props.user.age ?? '',
  location: props.user.location ?? '',
  university: props.user.university ?? '',
  study_field: props.user.study_field ?? '',
  study_year: props.user.study_year ?? '',
  specialization: props.user.specialization ?? '',
  study_field_ids: [...(props.user.study_field_ids ?? [])],
  preferred_cities: [...(props.user.preferred_cities ?? [])],
})

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    profileForm.defaults({
      first_name: props.user.first_name ?? '',
      last_name: props.user.last_name ?? '',
      age: props.user.age ?? '',
      location: props.user.location ?? '',
      university: props.user.university ?? '',
      study_field: props.user.study_field ?? '',
      study_year: props.user.study_year ?? '',
      specialization: props.user.specialization ?? '',
      study_field_ids: [...(props.user.study_field_ids ?? [])],
      preferred_cities: [...(props.user.preferred_cities ?? [])],
    }).reset()
    cancelPhotoPending()
    scrollToFocusSection()
  }
})

function scrollToFocusSection() {
  if (!props.focusSection) return
  nextTick(() => {
    document.getElementById(`profile-section-${props.focusSection}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

const displayPhotoUrl = computed(() => previewUrl.value ?? props.user.photo_url)
const hasPhotoPending = computed(() => Boolean(pendingFile.value))
const photoError = computed(() => localError.value || photoForm.errors.photo)
const fieldError = (field) => profileForm.errors[field]

function revokePreview() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

function validatePhoto(file) {
  const name = file.name.toLowerCase()
  const ok = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
    || name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png') || name.endsWith('.webp')
  if (!ok) {
    localError.value = t('student.profile.photo.errors.invalidType')
    return false
  }
  if (file.size > MAX_SIZE_BYTES) {
    localError.value = t('student.profile.photo.errors.tooLarge')
    return false
  }
  return true
}

function setPendingPhoto(file) {
  localError.value = null
  if (!validatePhoto(file)) return
  revokePreview()
  pendingFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

function onDrop(event) {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) setPendingPhoto(file)
}

function onFileSelected(event) {
  const file = event.target.files?.[0]
  if (file) setPendingPhoto(file)
  event.target.value = ''
}

function cancelPhotoPending() {
  pendingFile.value = null
  revokePreview()
  localError.value = null
  photoForm.clearErrors()
}

function saveProfile() {
  profileForm.patch(ROUTES.STUDENT_PROFILE_UPDATE, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}

function saveAll() {
  if (hasPhotoPending.value) {
    photoForm.photo = pendingFile.value
    photoForm.post(ROUTES.STUDENT_PROFILE_PHOTO, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        cancelPhotoPending()
        if (profileForm.isDirty) {
          saveProfile()
        } else {
          emit('close')
        }
      },
    })
    return
  }
  if (profileForm.isDirty) {
    saveProfile()
    return
  }
  emit('close')
}

onBeforeUnmount(revokePreview)
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('student.profile.edit.title')"
    max-width-class="max-w-2xl"
    @close="emit('close')"
  >
    <div class="mb-6 flex flex-col items-center gap-3 sm:flex-row sm:items-start">
      <ProfileAvatar
        :photo-url="displayPhotoUrl"
        :first-name="profileForm.first_name"
        :last-name="profileForm.last_name"
        size-class="w-20 h-20 text-xl"
      />
      <div class="flex-1">
        <input
          ref="fileInput"
          type="file"
          accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
          class="sr-only"
          :aria-label="t('student.profile.photo.dropzone')"
          @change="onFileSelected"
        >
        <button
          type="button"
          class="text-primary text-sm font-medium hover:underline"
          @click="fileInput?.click()"
        >
          {{ t('student.profile.photo.change') }}
        </button>
        <div
          class="mt-3 rounded-xl border-2 border-dashed px-4 py-6 text-center text-additional text-sm"
          :class="isDragging ? 'border-primary bg-primary/5' : 'border-border'"
          @dragover.prevent
          @dragenter.prevent="isDragging = true"
          @dragleave="isDragging = false"
          @drop.prevent="onDrop"
        >
          {{ t('student.profile.photo.dropzone') }}
        </div>
        <p v-if="hasPhotoPending" class="mt-2 text-additional text-xs">
          {{ t('student.profile.photo.previewHint') }}
        </p>
        <p v-if="photoError" class="mt-2 text-error text-sm" role="alert">{{ photoError }}</p>
      </div>
    </div>

    <h3 class="mb-3 font-medium text-text text-sm">
      {{ t('student.profile.edit.basicData') }}
    </h3>
    <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" novalidate @submit.prevent>
      <BaseInput
        id="edit_first_name"
        v-model="profileForm.first_name"
        :label="t('student.profile.details.firstName')"
        :error="fieldError('first_name')"
      />
      <BaseInput
        id="edit_last_name"
        v-model="profileForm.last_name"
        :label="t('student.profile.details.lastName')"
        :error="fieldError('last_name')"
      />
      <div class="sm:col-span-2">
        <p class="text-additional text-sm">
          {{ t('student.profile.email.label') }}
        </p>
        <p class="mt-1 rounded-lg border border-border bg-background px-4 py-3 text-additional text-sm">
          {{ user.email }}
        </p>
        <p class="mt-1 text-additional text-xs">
          {{ t('student.profile.edit.emailReadonlyHint') }}
        </p>
      </div>
      <BaseInput
        id="edit_age"
        v-model="profileForm.age"
        class="max-w-36"
        stacked
        :label="t('student.profile.edit.ageLabel')"
        :error="fieldError('age')"
      />
      <BaseInput
        id="edit_location"
        v-model="profileForm.location"
        class="sm:col-span-2"
        stacked
        :label="t('student.profile.edit.locationLabel')"
        :error="fieldError('location')"
      />
    </form>

    <h3 class="mb-1 mt-6 font-medium text-text text-sm">
      {{ t('student.profile.edit.searchPreferences') }}
    </h3>
    <p class="mb-3 text-additional text-xs">
      {{ t('student.profile.edit.searchPreferencesHint') }}
    </p>
    <div class="grid grid-cols-1 gap-4">
      <DynamicMultiSelect
        id="edit_fields"
        v-model="profileForm.study_field_ids"
        :label="t('student.profile.details.fields')"
        :options="studyFields"
        :max="10"
        :placeholder="t('student.profile.details.fieldsPlaceholder')"
        :error="fieldError('study_field_ids')"
      />
      <ProfileTagInput
        id="edit_cities"
        v-model="profileForm.preferred_cities"
        :label="t('student.profile.details.cities')"
        :placeholder="t('student.profile.details.citiesPlaceholder')"
        :error="fieldError('preferred_cities')"
      />
    </div>

    <h3 id="profile-section-university" class="mb-3 mt-6 font-medium text-text text-sm">
      {{ t('student.profile.edit.education') }}
    </h3>
    <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" novalidate @submit.prevent>
      <div class="sm:col-span-2">
        <label for="edit_university" class="mb-1 block text-additional text-sm">
          {{ t('student.profile.edit.university') }}
        </label>
        <select
          id="edit_university"
          v-model="profileForm.university"
          class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text transition-all focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30"
          :aria-invalid="fieldError('university') ? true : undefined"
          :aria-describedby="fieldError('university') ? 'edit_university-error' : undefined"
        >
          <option
            v-for="university in universityOptions"
            :key="university"
            :value="university"
          >
            {{ university }}
          </option>
        </select>
        <p
          v-if="fieldError('university')"
          id="edit_university-error"
          class="mt-1 text-error text-sm"
          role="alert"
        >
          {{ fieldError('university') }}
        </p>
      </div>
      <BaseInput
        id="edit_study_field"
        v-model="profileForm.study_field"
        :label="t('student.profile.edit.studyField')"
        :error="fieldError('study_field')"
      />
      <BaseInput
        id="edit_study_year"
        v-model="profileForm.study_year"
        :label="t('student.profile.edit.studyYear')"
        :error="fieldError('study_year')"
      />
      <BaseInput
        id="edit_specialization"
        v-model="profileForm.specialization"
        class="sm:col-span-2"
        :label="t('student.profile.edit.specialization')"
        :error="fieldError('specialization')"
      />
    </form>

    <div id="profile-section-cv" class="mt-6 rounded-xl border border-border bg-background px-4 py-3 text-additional text-sm">
      {{ t('student.profile.edit.cvPlaceholder') }}
    </div>

    <div class="sticky bottom-0 z-30 -mx-6 mt-8 flex gap-3 border-t border-slate-300 bg-slate-200 px-6 pt-4 pb-4 shadow-[0_-8px_20px_rgba(15,23,42,0.1)]">
      <BaseButton
        type="button"
        variant="secondary"
        class="flex-1 justify-center sm:flex-none"
        @click="emit('close')"
      >
        {{ t('student.profile.actions.cancel') }}
      </BaseButton>
      <div class="flex flex-1 justify-end gap-3">
        <BaseButton
          v-if="hasPhotoPending"
          type="button"
          variant="secondary"
          class="hidden sm:inline-flex"
          @click="cancelPhotoPending"
        >
          {{ t('student.profile.photo.cancelPreview') }}
        </BaseButton>
        <BaseButton
          type="button"
          class="flex-1 justify-center sm:flex-none"
          :disabled="profileForm.processing || photoForm.processing"
          @click="saveAll"
        >
          {{ t('student.profile.edit.saveChanges') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
